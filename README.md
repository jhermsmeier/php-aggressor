# Aggressor

### Usage

```php
<?php
  
  // you can set the options two ways,
  // either through the constructor:
  $rss = new Aggressor(array(
    'charset' => 'utf-8',
    'userAgent' => 'SomeAgent/0.1'
  ));
  
  // or on the object itself:
  $rss->charset = 'utf-16';
  $rss->userAgent = 'SomeOtherAgent/0.1';
  
  // fetch a feed
  $rss->get( 'http://example.com/feed.rss' );
  
?>
```


### Dependencies

At least PHP 5.4 is needed.
The last PHP 5.3 compatible commit is ee5eceae2e40f4cfd895cbb4f80aca8eafd12120.


### Options

- `string Aggressor::$charset = 'utf-8'` must be a valid charset descriptor.

- `bool Aggressor::$CDATA = FALSE` sets whether CDATA tags should be stripped from the output
  or not.

- `bool Aggressor::$HTML = FALSE` sets whether HTML tags should be stripped from the output
  or not.

- `string Aggressor::$cache = FALSE` must be either a string depicting a path to a directory
  where the cached feeds are stored or something evaluating to a boolean false to indicate that no caching
  should take place.

- `int Aggressor::$expires = 120` sets the cache expiration in seconds.

- `int Aggressor::$itemLimit = 0` sets the number of items returned. Setting it to zero deactivates the limit.

- `string Aggressor::$dateFormat = 'r'` sets the date format used in returned object. Setting it to something that
  evaluates to false (e.g. an empty string) will prevent formatting times and dates given in the feed.

- `string Aggressor::$userAgent = 'RSS Aggressor'` sets the user agent to be used in the requests.


### Methods

- `object Aggressor::get( string $url )` returns the parsed feed.
- `string Aggressor::stripHTML( string $input )` returns input stripped from HTML tags.
- `object Aggressor::parse( string $xml )` returns parsed RSS XML input.