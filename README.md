BooXtreamClient
===============

A client class in PHP for use with the BooXtream webservice.

- Has the ability to upload an epub file to the BooXtream webservice
- Alternatively specify an epub file stored on the BooXtream storage servers
- Can return an epub file, a (converted) mobi file or an xml containing one or more downloadlinks

### Installing via Composer

BooXtreamClient is not available in Packagist, but you can specify this repository in your composer.json:

```javascript
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/BooXtream/BooXtreamClient"
        }
    ],
    "require": {
        "icontact/booxtreamclient": "~v0.6"
    }
}
```

Alternatively you can just download the package and run ```composer install``` to get the requirements.

The only requirement at the moment is [Guzzle](http://guzzle.readthedocs.org/en/latest/index.html).

If you do not wish to use Composer you will need to fulfill the dependencies on your own.

### Usage

You will need:
- a username and API key for the service
- an array of options (refer to the BooXtream web service API documentation)
- an epub file or a file stored on the BooXtream service

```php
require('vendor/autoload.php');

use \Icontact\BooXtreamClient\BooXtreamClient;
use \GuzzleHttp\Client;

// a guzzle client with the booxtream web service url as a base_url
$Guzzle = new Client(['base_url' => 'https://service.booxtream.com']);

// You will need an epub file here
$EpubFile = new EpubFile('epubfile', fopen('path/to/your/file.epub, 'r'));

// create the BooXtream Client, you will need a username and an API key
$BooXtream = new BooXtreamClient($Guzzle, 'username', 'apikey');
```

We're now going to create the request, and load it with the EpubFile.

The first parameter can be either 'epub', 'mobi' or 'xml'. In the first two cases a file will be returned by the service, in the case of 'xml' you will receive one or two, depending on your settings, downloadlinks (aka delivery platform).

```php
// create a request with the epubfile
$BooXtream->createRequest('epub', $EpubFile);

// set the options as an array, refer to the documentation for more information on the options
$BooXtream->setOptions([
    'customername' => 'Foo Bar',
    'referenceid' => 'bazdibzimgir12345',
    'languagecode' => 1033
]);

// and send
$response = $BooXtream->send();
```

A request with a stored file is slightly different. Instead of an instance of EpubFile you just need to provide the name of the file (with or without .epub extension).

```php
$BooXtream->createRequest('epub');
```

And the stored file:

```php
$BooXtream->setStoredFile('filename');
```

=== Options

The available options are as follows. Refer to the API Documentation for details:

required:
- customername (string)
- customeremailaddress (string)
- referenceid (string)
- languagecode (int)

additionaly required if using xml (aka delivery platform):
- expirydays (int)
- downloadlimit (int)

optional:
- exlibris (bool), default: false
- chapterfooter (bool), default: false
- disclaimer (bool), default: false
- showdate (bool), default: false

optional if using xml (aka delivery platform):
- epub (bool), default: true
- kf8mobi (bool), default: false

=== Response
The response is an array that always contains the following three fields:
- raw, this contains the raw returned data (epub, mobi or xml)
- statuscode, the HTTP status code returned by BooXtream (anything other than 200 is an error)
- reasonphrase, the second part of the HTTP response, e.g.: OK or NOT FOUND.

In the case of a returned epub or mobi there will also be a field called
- content-type, containing the content-type of the returned file

In the case of xml (in the case of an error (remember, anything other than a code 200 is an error) there will also be xml), you will encounter the following fields:
- Request, containing information about the request you made (options, etc)
- Response, containing either a set of downloadlinks or more information on an error
