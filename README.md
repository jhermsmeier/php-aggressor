# Aggressor

### Usage

```php
// you can set the options two ways,
// either through the constructor:
$feed = new Aggressor(array(
  'charset' => 'utf-8',
  'userAgent' => 'SomeAgent/0.1'
));

// or on the object itself:
$feed->charset = 'utf-16';
$feed->userAgent = 'SomeOtherAgent/0.1';

// 

```


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

- `int Aggressor::$itemLimit = 0` sets the number of item returned. Setting it to zero deactivates the limit.

- `string Aggressor::$dateFormat = 'r'` sets the date format used in returned object. Setting it to something that
  evaluates to false (e.g. an empty string) will prevent formatting times and dates given in the feed.

- `string Aggressor::$userAgent = 'RSS Aggressor'` sets the user agent to be used in the requests.


### Methods

- `object Aggressor::get( string $url )` returns the parsed feed.
- `string Aggressor::stripHTML( string $input )` returns input stripped from HTML tags.
- `object Aggressor::parse( string $xml )` returns parsed RSS XML input.