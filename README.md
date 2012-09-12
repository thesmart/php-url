php-url
=======

Create, manipulate, and output any URL easily. Convert between relative and absolute URLs. Map URLs to their canonical equivalent.

Usage
-----

###Construct a URL from a string

```php
$url = new Url('http://example.com/');
echo (string)$url;
```

```
http://example.com/
```

###Mutate the URL in different ways
```php
$url = new Url();
$url->setScheme('https://')->setHost('www.reddit.com')->setPath('/r/programming');
echo (string)$url;
```

```
https://www.reddit.com/r/programming
```

###Access the arguments from a URL

**NOTE**: automatically alphabetizes the arguments.

```php
$url = new Url('http://example.com?x=0&a=1&b=2');
var_dump($url->getQuery());
var_dump($url->getQueryStr());
```

```
array(3) {
  ["a"]=>
  string(1) "1"
  ["b"]=>
  string(1) "2"
  ["x"]=>
  string(1) "0"
}
string(11) "a=1&b=2&x=0"
```

###Handles trailing-slash like you would expect

```php
$url = new Url('http://example.com');
var_dump((string)$url);
$url = new Url('http://example.com/');
var_dump((string)$url);
$url = new Url('http://example.com');
$url->setPath('/');
var_dump((string)$url);
```

```
string(18) "http://example.com"
string(19) "http://example.com/"
string(19) "http://example.com/"
```