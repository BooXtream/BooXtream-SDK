BooXtreamClient
===============

A client class in PHP for use with the BooXtream webservice.

- Has the ability to upload an epub file to the BooXtream webservice
- Alternatively specify an epub file stored on the BooXtream storage servers
- Can return an epub file, a (converted) mobi file or an xml containing one or more downloadlinks

### Installing via Composer

BooXtreamClient is available in Packagist, just add it to your composer.json

```javascript
{
    "require": {
        "icontact/booxtreamclient": "~2.0"
    }
}
```

Alternatively you can just download the package and run ```composer install``` to get the requirements.

The only requirements at the moment are PHP 7.2.5 and up and [Guzzle](http://guzzle.readthedocs.org/en/latest/index.html).

If you do not wish to use Composer you will need to fulfill the dependencies on your own.

### Usage

You will need:
- a username and API key for the service
- an array of options (refer to the BooXtream web service API documentation)
- an epub file or a file stored on the BooXtream service

The type parameter can be either 'epub', 'mobi' or 'xml'. In the first two cases a file will be returned by the service, in the case of 'xml' you will receive one or two downloadlinks, depending on your settings (aka delivery platform).

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
// add a file location to the epub file you want to watermark
$BooXtream->setEpubFile('/path/to/your/file.epub');

// and send
$Response = $BooXtream->send();
```

A request with a stored file is slightly different. Instead of adding an epubfile you just need to provide the name of the file (with or without .epub extension):

```php
$BooXtream->setStoredEpubFile('filename');
```

### Options

The available options are as follows. Refer to the API Documentation for details:

#### required:
- customername (string)
- customeremailaddress (string)
- referenceid (string)
- languagecode (int)

#### additionaly required if using xml (aka delivery platform):
- expirydays (int)
- downloadlimit (int)

#### optional:
- exlibrisfont (string), this should contain either 'sans', 'serif' or 'script'
- exlibris (bool), default: false
- chapterfooter (bool), default: false
- disclaimer (bool), default: false
- showdate (bool), default: false

#### optional if using xml (aka delivery platform):
- epub (bool), default: true
- kf8mobi (bool), default: false

#### custom ex libris
It is also possible to set a custom ex libris file according to the specifications in the API Documentation.
```php
$BooXtream->setExlibrisFile('filename');
```

### Response

The BooXtreamClient returns an object of the type of [GuzzleHttp\Psr7\Response](https://docs.guzzlephp.org/en/latest/index.html)

The response always contains a statuscode (```$Response->getStatusCode();```). If the request was successful this will be 200. Any other status code is an error and will throw an Exception of the type of *GuzzleHttp\Exception\ClientException*. You can retrieve a Response object as follows:
```php
try {
  $Response = $BooXtream->send();
} catch (ClientException $e) {
  $Response = $e->getResponse();
}
```
Check the HTTP Reason (```$Response->getReasonPhrase();```) for more information.

#### Epub/Mobi
If you requested an epub or mobi file this can be accessed by reading the body (```$Response->getBody();```). The body is a stream, refer to the [PHP Documentation](http://php.net/stream) for more information on how to access it. Furthermore you can access the file's content-type with ```$Response->getHeader('content-type');```.

#### XML or error
If you requested xml (aka delivery platform) or if an error occurred more information can be found by accessing the body (```$Response->getBody();```). The body is a stream, refer to the [PHP Documentation](http://php.net/stream) for more information on how to access it.

The XML looks like this:
- Request, containing information about the request you made (options, etc)
- Response, containing either a set of downloadlinks or more information on an error
