<?php
namespace Icontact\BooXtreamClient;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Post\PostFile;
use GuzzleHttp\ClientInterface;

/**
 * Class BooXtreamClient
 * Use to connect to and use the BooXtream webservice
 */
class BooXtreamClient implements BooXtreamClientInterface {
    const BASE_URL = 'https://service.booxtream.com';

    private $guzzle;
    private $username;
    private $apikey;
    private $type;
    private $options;
    private $epubfile;
    private $storedfile;
    private $exlibrisfile;

    /**
     * @param ClientInterface $guzzle
     * @param string $username
     * @param string $apikey
     */
    public function __construct(ClientInterface $guzzle, $username, $apikey) {
        $this->guzzle = $guzzle;
        $this->guzzle->setDefaultOption('auth', [$username, $apikey]);

        $this->username = $username;
        $this->apikey = $apikey;
    }

    /**
     * @param string $type
     */
    public function createRequest($type) {
        switch($type) {
            case 'xml':
            case 'epub':
            case 'mobi':
                $this->type = $type;
                break;
            default:
                throw new \InvalidArgumentException('invalid type '.$type);
        }
    }

    /**
     * @param string $file
     */
    public function setEpubFile($file) {
        if(!is_null($this->epubfile)) {
            throw new \RuntimeException('storedfile set but also trying to '.__CLASS__);
        }
        $this->epubfile = $this->createPostFile('epubfile', $file);
    }

    /**
     * @param string $file
     */
    public function setExlibrisFile($file) {
        $this->exlibrisfile = $this->createPostFile('exlibrisfile', $file);
    }

    /**
     * @param string $name
     * @param string $file
     * @return PostFile
     */
    private function createPostFile($name, $file) {
        if(!file_exists($file)) {
            throw new \RuntimeException('file '.$file.' not found while setting '.$name);
        }
        $PostFile = new PostFile($name,fopen($file,'r'));
        return $PostFile;
    }

    /**
     * @param string $storedfile
     */
    public function setStoredFile($storedfile) {
        if(!is_null($this->epubfile)) {
            throw new \RuntimeException('epubfile set but also trying to '.__CLASS__);
        }

        // remove .epub from storedfile
        $pos = strrpos(strtolower($storedfile), '.epub');
        if($pos) {
            $storedfile = substr($storedfile, 0, $pos);
        }

        // check if stored file exists
        $request = $this->guzzle->createRequest('POST', self::BASE_URL . '/storedfiles/' . $storedfile);
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
     * @return ResponseInterface
     */
    public function send() {
        if(is_null($this->storedfile) && is_null($this->epubfile)) {
            throw new \RuntimeException('storedfile or epubfile not set');
        }
        if(is_null($this->options)) {
            throw new \RuntimeException('options not set');
        }
        if(is_null($this->storedfile)) {
            $action = self::BASE_URL . '/booxtream.' . $this->type;
        } else {
            $action = self::BASE_URL . '/storedfiles/' . $this->storedfile . '.' . $this->type;
        }

        $request = $this->guzzle->createRequest('POST', $action);

        // add stuff to the body
        $postBody = $request->getBody();
        $postBody->replaceFields($this->options);
        if(is_null($this->storedfile)) {
            $postBody->addFile($this->epubfile);
        }
        if(!is_null($this->exlibrisfile)) {
            $postBody->addFile($this->exlibrisfile);
        }


        // force multipart, can't do this with header
        $postBody->forceMultipartUpload(true);

        try {
            $response = $this->guzzle->send($request);
        } catch(ClientException $e) {
            $response = $e->getResponse();
        }
        return $response;
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
            if(is_null($this->exlibrisfile)) {
                $options['exlibris'] = $this->checkBool('exlibris', $options['exlibris']);
            } else {
                // force this, there is no use to setting an exlibrisfile without setting exlibris to true
                $options['exlibris'] = 1;
            }
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
}