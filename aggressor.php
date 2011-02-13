<?php

class aggressor {
  
  # parameters
  public $charset = 'utf-8'; # default charset
  public $rss_charset = null;
  public $CDATA = false; # allow CDATA tags
  public $HTML = false; # allow HTML in output
  
  public $cache = false; # cache feeds?
  public $expires = 120; # cache expiration in seconds
  
  public $item_limit = 0; # max items in output
  public $date_format = 'r'; # date format ( php date() function )
  
  public $info = array(); # http transfer info
  
  # private properties
  private $channeltags = array('title','link','description','language','copyright','managingEditor','webMaster','lastBuildDate','rating','docs');
  private $itemtags = array('title','link','description','author','category','comments','enclosure','guid','pubDate','source');
  private $imagetags = array('title','url','link','width','height');
  private $textinputtags = array('title','description','name','link');
  
  /** fetch a feed, parse & return assoc array */
  function get( $url ) {
    if( $this->cache ) {
      # cache enabled
      $cache_file = $this->cache.'feed_'.md5($url).'.cache';
      $time_diff = ( file_exists($cache_file) ) ? ( time() - filemtime($cache_file) ) : $this->expires + 1;
      # if cache is fresh enough, return cached array
      if( $time_diff < $this->expires ) {
        $result = unserialize(file_get_contents($cache_file));
      }
      else {
        # cache is to old, replace with new copy
        $result = $this->fetch( $url );
        file_put_contents( $cache_file, serialize($result) );
      }
    }
    else {
      # cache disabled
      $result = $this->fetch( $url );
    }
    return $result;
  }
  
  /** fetch a feed */
  function fetch( $url ) {
    if( function_exists('curl_init') ) {
      # handle it with curl
      $ch = curl_init( $url );
      curl_setopt_array( $ch, array(
        CURLOPT_FAILONERROR => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => 'RSS Feed Fetcher'
      ));
      $xml = curl_exec( $ch );
      $this->info = curl_getinfo($ch); # this is so optional
      curl_close( $ch );
    }
    else {
      # handle it via fopen
      $xml = '';
      if( $f = @fopen( $url, 'r' ) ) {
        while( !feof($f) ) $xml.= fgets( $f, 4096 );
        fclose( $f );
      }
      else return false;
    }
    return $this->parse( $xml );
  }
  
  /** specialized preg_match function */
  function regex( $pattern, $subject ) {
    # run preg match
    preg_match( $pattern, $subject, $match );
    # if result exists, process it
    if( isset($match[1]) ) {
      # strip CDATA if set
      if( !$this->CDATA ) {
        $match[1] = strtr( $match[1], array('<![CDATA['=>'',']]>'=>'') );
      }
      # if charset is set, convert to it
      if( $this->charset && ($this->rss_charset != $this->charset) ) {
        $match[1] = iconv( $this->rss_charset, $this->charset, $match[1] );
      }
      return trim($match[1]);
    }
    else return '';
  }
  
  /** parse the rss xml */
  function parse( $xml ) {
    
    $result = array();
    
    # merge transfer info into result
    # this is so optional, too
    $result = array_merge( $result, $this->info );
    
    # figure out xml document encoding
    $result['encoding'] = $this->regex( "'encoding=[\'\"](.*?)[\'\"]'si", $xml );
    # set rss charset if available (used in $this->regex())
    $this->rss_charset = (!empty($result['encoding'])) ? $result['encoding'] : $this->charset ;
    
    # parse channel info
    preg_match( "'<channel.*?>(.*?)</channel>'si", $xml, $channel );
    foreach( $this->channeltags as $channeltag ) {
      $temp = $this->regex( "'<$channeltag.*?>(.*?)</$channeltag>'si", $channel[1] );
      $result[$channeltag] = ( !empty($temp) ) ? $temp : null;
    }
    
    # check for date & format it accordingly
    if( !$this->date_format && isset($result['lastBuildDate']) ) {
      $result['lastBuildDate'] = date( $this->date_format, strtotime($result['lastBuildDate']) );
    }
    
    # parse textinput info
    preg_match( "'<textinput(|[^>]*[^/])>(.*?)</textinput>'si", $xml, $textinfo );
    if( !empty($textinfo) ) {
      foreach( $this->textinputtags as $textinputtag ) {
        $temp = $this->regex( "'<$textinputtag.*?>(.*?)</$textinputtag>'si", $textinfo[2] );
        $result[$textinputtag] = ( !empty($temp) ) ? $temp : null;
      }
    }
    
    # parse image info
    preg_match( "'<image.*?>(.*?)</image>'si", $xml, $imageinfo );
    if( !empty($imageinfo) ) {
      foreach( $this->imagetags as $imagetag ) {
        $temp = $this->regex( "'<$imagetag.*?>(.*?)</$imagetag>'si", $imageinfo[1] );
        $result['image_'.$imagetag] = ( !empty($temp) ) ? $temp : null;
      }
    }
    
    # parse items
    preg_match_all( "'<item(| .*?)>(.*?)</item>'si", $xml, $items );
    $items = $items[2];
    $result['items'] = array();
    $i = 0;
    
    foreach( $items as $item ) {
      if( $i < $this->item_limit || $this->item_limit === 0 ) {
        foreach( $this->itemtags as $itemtag ) {
          $temp = $this->regex( "'<$itemtag.*?>(.*?)</$itemtag>'si", $item );
          $result['items'][$i][$itemtag] = ( !empty($temp) ) ? $temp : null;
        }
        
        if( !$this->HTML && isset($result['items'][$i]['description']) ) {
          $result['items'][$i]['description'] = strip_tags( html_entity_decode($result['items'][$i]['description'],ENT_QUOTES,$this->charset) );
        }
        if( !$this->HTML && isset($result['items'][$i]['title']) ) {
          $result['items'][$i]['title'] = strip_tags( html_entity_decode($result['items'][$i]['title'],ENT_QUOTES,$this->charset) );
        }
        if( !$this->date_format && isset($result['items'][$i]['pubDate']) ) {
          $result['items'][$i]['pubDate'] = date( $this->date_format, strtotime($result['items'][$i]['pubDate']) );
        }
        $i++;
      }
      else break;
    }
    
    $result['item_count'] = $i;
    return (object) $result;
    
  }
  
}

?>