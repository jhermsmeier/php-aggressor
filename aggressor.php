<?php

class Aggressor {
  
  // default charset
  public $charset = 'utf-8';
  // CDATA tags in output?
  public $CDATA = FALSE;
  // HTML in output?
  public $HTML = FALSE;
  
  // cache feeds?
  public $cache = FALSE;
  // cache expiration in seconds
  public $expires = 120;
  
  // max items in output (0 = limitless)
  public $itemLimit = 0;
  // output date format (date())
  public $dateFormat = 'r';
  
  // user agent
  public $userAgent = 'RSS Aggressor';
  
  // detected charset of the rss xml
  protected $RSSCharset = NULL;
  
  // tag lists
  protected $channelTags = [ 'title', 'link', 'description', 'language', 'copyright', 'managingEditor', 'webMaster', 'lastBuildDate', 'rating', 'docs' ];
  protected $itemTags    = [ 'title', 'link', 'description', 'author', 'category', 'comments', 'enclosure', 'guid', 'pubDate', 'source' ];
  protected $imageTags   = [ 'title', 'url', 'link', 'width', 'height' ];
  protected $inputTags   = [ 'title', 'description', 'name', 'link' ];
  
  // ...
  public function __construct( $options = NULL ) {
    foreach( $options as $key => $value ) {
      $this->$key = $value;
    }
  }
  
  // fetch a feed and parse, return assoc array
  public function get( $url ) {
    if( $this->cache ) {
      $file = "{$this->cache}/feed_".md5( $url ).'.cache';
      $diff = file_exists( $file ) ? time() - filetime( $file ) : ++$this->expires;
      if( $diff < $this->expires ) {
        return unserialize( file_get_contents( $file ) );
      }
      else {
        $feed = $this->fetch( $url );
        file_put_contents( $file, serialize( $feed ) );
        return $feed;
      }
    }
    else {
      return $this->fetch( $url );
    }
  }
  
  // fetch a feed
  public function fetch( $url ) {
    $ch = curl_init( $url );
    curl_setopt_array( $ch, [
      CURLOPT_FAILONERROR => TRUE,
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_USERAGENT => $this->userAgent
    ]);
    $xml = curl_exec( $ch );
    $info = curl_getinfo( $ch );
    curl_close();
    return $this->parse( $xml, $info );
  }
  
  // regex matching with extras
  protected function match( $pattern, $subject ) {
    if( preg_match( $pattern, $subject, $matches ) && isset( $matches[1] ) ) {
      $match = &$matches[1];
      if( $this->CDATA )
        $match = strtr( $match, [ '<![CDATA[' => '', ']]>' => '' ] );
      if( $this->charset && ( $this->RSSCharset != $this->charset ) )
        $match = iconv( $this->RSSCharset, $this->charset, $match );
      return trim( $match );
    }
    else return '';
  }
  
  // ...
  public function stripHTML( $input ) {
    return strip_tags( html_entity_decode( $input, ENT_QUOTES, $this->charset ) );
  }
  
  // parse the rss xml
  public function parse( $xml, $info = NULL ) {
    // include info, if we have it
    $result = (object) ( is_array( $info ) ? $info : [] );
    // figure out xml encoding
    $result->encoding = $this->match( '{encoding=[\'"](.*?)[\'"]}si', $xml );
    // set rss charset if available
    $this->RSSCharset = !empty( $result->encoding ) ? &$result->encoding : &$this->charset;
    // parse channel info
    if( preg_match( '{<channel.*?>(.*?)</channel>}si', $xml, $matches ) ) {
      foreach( $this->channelTags as &$tag )
        $result->$tag = $this->match( "{<$tag.*?>(.*?)</$tag>}si", $matches[1] );
    }
    // check for date and format it accordingly
    if( $this->dateFormat && isset( $result->lastBuildDate ) )
      $result->lastBuildDate = date( $this->dateFormat, strtotime( $result->lastBuildDate ) );
    // parse textinput info
    if( preg_match( '{<textinput(?:|[^>]*[^/])>(.*?)</textinput>}si', $xml, $matches ) ) {
      foreach( $this->inputTags as &$tag )
        $result->$tag = $this->match( "{<$tag.*?>(.*?)</$tag>}si", $matches[1] );
    }
    // parse image info
    if( preg_match( '{<image.*?>(.*?)</image>}si', $xml, $matches ) ) {
      foreach( $this->imageTags as &$tag )
        $result->["image_$tag"] = $this->match( "{<$tag.*?>(.*?)</$tag>}si", $matches[1] );
    }
    // parse items
    if( preg_match_all( '{<item(?:| .*?)>(.*?)</item>}si', $xml, $matches ) ) {
      $matches = $matches[1];
      $result->items = [];
      $i = 0;
      foreach( $matches as &$item ) {
        if( $i < $this->itemLimit || $this->itemLimit === 0 ) {
          $current = &$result->items[$i] = new stdClass;
          foreach( $this->itemTags as &$tag )
            $current->$tag = $this->match( "{<$tag.*?>(.*?)</$tag>}si", $item );
          if( $this->HTML && isset( $current->description ) )
            $current->description = $this->stripHTML( $current->description );
          if( $this->HTML && isset( $current->title ) )
            $current->title = $this->stripHTML( $current->title );
          if( $this->dateFormat && isset( $current->pubDate ) )
            $current->pubDate = date( $this->dateFormat, strtotime( $current->pubDate ) );
          $i++;
        }
        else break;
      }
      $result->itemCount = $i;
      return $result;
    }
  }
  
}

?>