BooXtreamClient
===============

A client class in PHP for use with the BooXtream webservice.

- Watermarks EPUB and PDF files; the service picks the format from the file extension
- Upload a file, or specify a file stored on the BooXtream storage servers
- Returns the watermarked file directly, or an xml containing a downloadlink

### Installing

```
composer require icontact/booxtreamclient
```

Requires PHP 8.3 and up and [Guzzle](http://guzzle.readthedocs.org/en/latest/index.html).

### Upgrading from 2.x

3.0 requires PHP 8.3+ and adds native parameter and return types (`declare(strict_types=1)`):
- all setters take a `string` and, like `Options::parseOptions()`, return `void` (they throw on failure); `send()` returns `Psr\Http\Message\ResponseInterface`;
- `BooXtreamClientInterface` no longer declares the constructor;
- PDF support; the API is format-neutral: `setEpubFile()` is `setFile()`, `setStoredEpubFile()` is `setStoredFile()`, and the `epub` type is `direct`;
- the `epub` option is gone; the service always delivers the file in its own format;
- `setStoredFile()` only strips a trailing `.epub` (`book.epub.v2` is no longer cut to `book`);
- mobi is no longer supported: the `mobi` type throws an `InvalidArgumentException`;
- the BooXtream service no longer supports the `kf8mobi` option; requests that send it fail;
- the not-found `InvalidArgumentException` from a stored-file check now carries the Guzzle exception as `getPrevious()`.

### Usage

You will need:
- a username and API key for the service
- an array of options (refer to the BooXtream web service API documentation)
- an EPUB or PDF file, or a file stored on the BooXtream service

The type parameter can be either 'direct' or 'xml'. With 'direct' the watermarked file is returned by the service, with 'xml' you will receive a downloadlink (aka delivery platform).

```php
require('vendor/autoload.php');

use \Icontact\BooXtreamClient\BooXtreamClient;
use \Icontact\BooXtreamClient\Options;
use \GuzzleHttp\Client;

// a guzzle client
$guzzle = new Client();

// an options object, refer to the documentation for more information on the options
$options = new Options([
    'customername' => 'Foo Bar',
    'referenceid' => 'bazdibzimgir12345',
    'languagecode' => 1033
]);

// create the BooXtream Client
$type = 'xml'; // returns downloadlinks
$credentials = ['username', 'apikey']; // you will need a username and an API key
$BooXtream = new BooXtreamClient($type, $options, $credentials, $guzzle);
```

We're now going to send the request.

```php
// add a file location to the EPUB or PDF file you want to watermark
$BooXtream->setFile('/path/to/your/file.epub');

// and send
$Response = $BooXtream->send();
```

A request with a stored file is slightly different. Instead of adding a file you just need to provide the name of the file (an EPUB with or without .epub extension, a PDF with .pdf):

```php
$BooXtream->setStoredFile('filename');
```

### Options

The available options are as follows. Refer to the API Documentation for details:

#### required:
- customername and/or customeremailaddress (string), at least one
- referenceid (string)
- languagecode (int)

#### used with xml (aka delivery platform) and with every PDF:
- expirydays (int), default: 30
- downloadlimit (int), default: 3

#### optional:
- exlibrisfont (string), this should contain either 'sans', 'serif' or 'script'
- exlibris (bool), default: false
- chapterfooter (bool), default: false
- disclaimer (bool), default: false
- showdate (bool), default: false

#### custom ex libris
It is also possible to set a custom ex libris file according to the specifications in the API Documentation.
```php
$BooXtream->setExlibrisFile('filename');
```

An ex libris stored on the BooXtream storage servers can be used as well. Pass it with its `.png` extension: the service adds `.epub` to a stored name without a known extension, so `logo` is looked up as `logo.epub`.
```php
$BooXtream->setStoredExlibrisFile('logo.png');
```

### Response

The BooXtreamClient returns an object of the type of [GuzzleHttp\Psr7\Response](https://docs.guzzlephp.org/en/latest/index.html)

The response always contains a statuscode (```$Response->getStatusCode();```). If the request was successful this will be 200. Any other status code is an error and will throw an Exception of the type of *GuzzleHttp\Exception\BadResponseException* (a *ClientException* for 4xx, a *ServerException* for 5xx). You can retrieve a Response object as follows:
```php
try {
  $Response = $BooXtream->send();
} catch (\GuzzleHttp\Exception\BadResponseException $e) {
  $Response = $e->getResponse();
}
```
Check the HTTP Reason (```$Response->getReasonPhrase();```) for more information.

#### Direct
If you requested a direct download this can be accessed by reading the body (```$Response->getBody();```). The body is a stream, refer to the [PHP Documentation](http://php.net/stream) for more information on how to access it. Furthermore you can access the file's content-type with ```$Response->getHeader('content-type');```.

#### XML or error
If you requested xml (aka delivery platform) or if an error occurred more information can be found by accessing the body (```$Response->getBody();```). The body is a stream, refer to the [PHP Documentation](http://php.net/stream) for more information on how to access it.

The XML looks like this:
- Request, containing information about the request you made (options, etc)
- Response, containing either a set of downloadlinks or more information on an error
