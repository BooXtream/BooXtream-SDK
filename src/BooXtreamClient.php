<?php
namespace Icontact\BooXtreamClient;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Post\PostBody;
use GuzzleHttp\Post\PostFileInterface;
use Icontact\BooXtreamClient\BooXtreamInterface;
use GuzzleHttp\ClientInterface;

/**
 * Class BooXtreamClient
 * Use to connect to and use the BooXtream webservice
 */

class BooXtreamClient implements BooXtreamInterface {
    private $guzzle;
    private $username;
    private $apikey;
    private $type;
    private $options;
    private $epubfile;
    private $storedfile;

    /**
     * @param ClientInterface $guzzle
     * @param $username
     * @param $apikey
     */
    public function __construct(ClientInterface $guzzle, $username, $apikey) {
        $this->guzzle = $guzzle;
        $this->guzzle->setDefaultOption('auth', [$username, $apikey]);

        $this->username = $username;
        $this->apikey = $apikey;
    }


    /**
     * @param string $type
     * @param PostFileInterface $file
     */
    public function createRequest($type, PostFileInterface $file = NULL) {
        switch($type) {
            case 'xml':
            case 'epub':
            case 'mobi':
                $this->type = $type;
                break;
            default:
                throw new \RuntimeException('invalid type '.$type);
        }
        $this->epubfile = $file;
    }

    public function setStoredFile($storedfile) {
        if(!is_null($this->epubfile)) {
            throw new \RuntimeException('epubfile exists but also trying to setStoredFile');
        }

        // remove .epub from storedfile
        $pos = strrpos(strtolower($storedfile), '.epub');
        if($pos) {
            $storedfile = substr($storedfile, 0, $pos);
        }

        // check if stored file exists
        $request = $this->guzzle->createRequest('POST', '/storedfiles/' . $storedfile);
        $query = $request->getQuery();
        $query->set('exists', null);
        try {
            $response = $this->guzzle->send($request);
        } catch (ClientException $e) {
            if($e->getCode() === 404) {
                throw new \RuntimeException('storedfile does not exist');
            }
            throw $e;
        }

        if($response->getStatusCode() === 200) {
            // set the storedfile and alter the action
            $this->storedfile = $storedfile;
            return true;
        } else {
            throw new \RuntimeException('storedfile does not exist');
        }
    }

    /**
     * @param array $options
     */
    public function setOptions($options) {
        if(!is_array($options)) {
            throw new \InvalidArgumentException('options expects an array');
        }
        $options = array_replace_recursive($this->getDefaultOptions(), $options);

        if($this->parseOptions($options)) {
            $this->options = $options;
        }
    }

    /**
     * @return array
     */
    public function send() {
        if(is_null($this->options)) {
            throw new \RuntimeException('options not set');
        }
        if(is_null($this->storedfile)) {
            $action = '/booxtream.' . $this->type;
        } else {
            $action = '/storedfiles/' . $this->storedfile . '.' . $this->type;
        }

        $request = $this->guzzle->createRequest('POST', $action);

        // add stuff to the body
        $postBody = $request->getBody();
        $postBody->replaceFields($this->options);
        if(is_null($this->storedfile)) {
            $postBody->addFile($this->epubfile);
        }

        // force multipart, can't do this with header
        $postBody->forceMultipartUpload(true);

        try {
            $response = $this->guzzle->send($request);
        } catch(ClientException $e) {
            $response = $e->getResponse();
        }
        return $this->parseResponse($response);
    }

    /**
     * @param array $options
     * @return bool
     */
    private function parseOptions($options) {
        try {
            // check required options
            if(!isset($options['customername']) && !isset($options['customeremailaddress'])) {
                throw new \InvalidArgumentException('required option customername or customeremailadress is not set');
            }
            if(!isset($options['referenceid'])) {
                throw new \InvalidArgumentException('required option referenceid is not set');
            }
            if(!isset($options['languagecode'])) {
                throw new \InvalidArgumentException('required option languagecode is not set');
            }


            // check additional required options for XML requests
            if ($this->type === 'xml') {
                if (!isset($options['expirydays']) || !is_int($options['expirydays'])) {
                    throw new \InvalidArgumentException('expirydays is not set');
                }
                if (!isset($options['downloadlimit']) || !is_int($options['downloadlimit'])) {
                    throw new \InvalidArgumentException('downloadlimit is not set');
                }
                if (!isset($options['epub'])) {
                    throw new \InvalidArgumentException('epub is not set');
                }
                if (!isset($options['kf8mobi'])) {
                    throw new \InvalidArgumentException('kf8mobi is not set');
                }

                // check options and translate booleans to 1 and 0
                $options['epub'] = $this->checkBool('epub', $options['epub']);
                $options['kf8mobi'] = $this->checkBool('kf8mobi', $options['kf8mobi']);
            }

            // check optional (but default) options and translate booleans to 1 and 0
            $options['exlibris'] = $this->checkBool('exlibris', $options['exlibris']);
            $options['chapterfooter'] = $this->checkBool('chapterfooter', $options['chapterfooter']);
            $options['disclaimer'] = $this->checkBool('disclaimer', $options['disclaimer']);
             $options['showdate'] = $this->checkBool('showdate', $options['showdate']);
        } catch (\InvalidArgumentException $e) {
            throw $e;
        }
        // checks out
        return true;
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return int
     */
    private function checkBool($name, $value) {
        if(!is_bool($value)) {
            throw new \InvalidArgumentException($name .' is set incorrectly');
        }
        return $value ? 1 : 0;
    }

    /**
     * @return array
     */
    private function getDefaultOptions() {
        $options = [
            'exlibris'          => false,
            'chapterfooter'     => false,
            'disclaimer'        => false,
            'showdate'          => false
        ];

        if($this->type === 'xml') {
            $options = array_merge($options, [
                'expirydays'    => 30,
                'downloadlimit' => 3,
                'epub'          => true,
                'kf8mobi'       => false
            ]);
        }

        return $options;
    }

    /**
     * @param ResponseInterface $response
     * @return array
     */
    private function parseResponse(ResponseInterface $response) {
        $body = $response->getBody();
        $code = $response->getStatusCode();
        $reason = $response->getReasonPhrase();
        $responseArray = [
            'raw' => $body->getContents(),
            'statuscode' => $code,
            'reasonphrase' => $reason
        ];


        if($this->type === 'xml' || $code !== 200) {
            $responseIterator = new \SimpleXmlIterator($body);
            foreach ($responseIterator as $name => $child) {
                $responseArray[$name] = get_object_vars($child);
            }
        } else {
            $responseArray['content-type'] = $response->getHeader('content-type');
        }

        return $responseArray;
    }
}