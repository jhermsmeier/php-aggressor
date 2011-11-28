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

```


### Options

#### Charset

`string Aggressor::$charset` must be a valid charset descriptor.

#### CDATA

`bool Aggressor::$CDATA` sets whether CDATA tags should be stripped from the output
or not.

#### HTML

`bool Aggressor::$HTML` sets whether HTML tags should be stripped from the output
or not.

#### Cache

`string Aggressor::$cache` must be either a string depicting a path to a directory
where the cached feeds are stored or something evaluating to a boolean false to indicate that no caching
should take place.

#### Expires

`int Aggressor::$expires` sets the cache expiration in seconds.

#### Item Limit

`int Aggressor::$itemLimit` sets the number of item returned. Setting it to zero deactivates the limit.

#### Date Format

`string Aggressor::$dateFormat` sets the date format used in returned object. Setting it to something that
evaluates to false (e.g. an empty string) will prevent formatting times and dates given in the feed.

#### User Agent

`string Aggressor::$userAgent` sets the user agent to be used in the requests.

