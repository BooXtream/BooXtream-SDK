<?php

namespace Icontact\BooXtreamClient;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;

/**
 * Class BooXtreamClient
 * Use to connect to and use the BooXtream webservice.
 */
class BooXtreamClient implements BooXtreamClientInterface
{
    /**
     * BooXtream location
     */
    const BASE_URL = 'https://service.booxtream.com';

    /**
     * @var array
     *
     * PHP 5.6 would allow us to define this array as a class constant, maybe later.
     */
    private $types = ['xml', 'epub', 'mobi'];

    /**
     * @var ClientInterface
     */
    private $guzzle;
    /**
     * @var array
     */
    private $authentication;
    /**
     * @var string
     */
    private $type;
    /**
     * @var Options
     */
    private $options;
    /**
     * @var array
     */
    private $files;
    /**
     * @var array
     */
    private $storedfiles;

    /**
     * @param string $type
     * @param Options $options
     * @param array $authentication
     * @param ClientInterface $guzzle
     *
     * return void
     */
    public function __construct($type, Options $options, array $authentication, ClientInterface $guzzle)
    {
        if ( ! in_array($type, $this->types)) {
            throw new \InvalidArgumentException('invalid type ' . $type);
        }

        $this->type    = $type;
        $this->guzzle  = $guzzle;
        $this->options = $options;
        $this->options->parseOptions($this->type === 'xml');

        $this->authentication = $authentication;
        $this->files          = [];
        $this->storedfiles    = [];
    }

    /**
     * @param string $file
     *
     * return bool
     */
    public function setEpubFile($file)
    {
        if (isset($this->storedfiles['epubfile'])) {
            throw new \RuntimeException('stored epubfile set but also trying to set local epubfile');
        }
        $this->files['epubfile'] = $this->checkFile('epubfile', $file);

        return true;
    }

    /**
     * @param string $file
     *
     * return bool
     */
    public function setExlibrisFile($file)
    {
        if (isset($this->storedfiles['exlibrisfile'])) {
            throw new \RuntimeException('stored exlibrisfile set but also trying to set local exlibrisfile');
        }
        $this->files['exlibrisfile'] = $this->checkFile('exlibrisfile', $file);

        return true;
    }

    /**
     * @param string $file
     *
     * @return array
     */
    private function checkFile($name, $file)
    {
        if ( ! file_exists($file) || ! is_readable($file)) {
            throw new \InvalidArgumentException('file ' . $file . ' not found or readable while setting ' . $name);
        }

        return [
            'name'     => $name,
            'filename' => basename($file),
            'contents' => fopen($file, 'r'),
        ];
    }

    /**
     * @param string $storedfile
     *
     * @return bool
     */
    public function setStoredEpubFile($storedfile)
    {
        if (isset($this->files['epubfile'])) {
            throw new \RuntimeException('epubfile set but also trying to set storedfile');
        }

        // remove epub extension
        $pos = strrpos(strtolower($storedfile), '.epub');
        if ($pos) {
            $storedfile = substr($storedfile, 0, $pos);
        }
        $this->storedfiles['epubfile'] = $this->checkStoredFile($storedfile);

        return true;
    }

    /**
     * @param string $storedfile
     *
     * @return bool
     */
    public function setStoredExlibrisFile($storedfile)
    {
        if (isset($this->files['exlibrisfile'])) {
            throw new \RuntimeException('exlibrisfile set but also trying to set storedfile');
        }

        $this->storedfiles['exlibrisfile'] = $this->checkStoredFile($storedfile);

        return true;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function send()
    {
        if ( ! isset($this->storedfiles['epubfile']) && ! isset($this->files['epubfile'])) {
            throw new \RuntimeException('storedfile or epubfile not set');
        }

        $multipart = $this->getMultipart();

        // set action
        $action = self::BASE_URL . '/booxtream.' . $this->type;
        if (isset($this->storedfiles['epubfile'])) {
            $action = self::BASE_URL . '/storedfiles/' . $this->storedfiles['epubfile'] . '.' . $this->type;
        }

        return $this->guzzle->request(
            'POST',
            $action,
            [
                'auth'      => $this->authentication,
                'multipart' => $multipart,
            ]
        );

    }

    /**
     * @param string $storedfile
     *
     * @return string
     */
    private function checkStoredFile($storedfile)
    {
        try {
            // check if stored file exists
            $this->guzzle->request(
                'GET',
                self::BASE_URL . '/storedfiles/' . $storedfile,
                [
                    'auth'  => $this->authentication,
                    'query' => [
                        'exists' => '',
                    ],
                ]
            );
        } catch (ClientException $e) {
            if ($e->getCode() === 404) {
                throw new \InvalidArgumentException('storedfile ' . $storedfile . ' does not exist');
            }
            throw $e;
        }
        return $storedfile;
    }

    /**
     * @return array
     */
    private function getMultipart()
    {
        $multipart = $this->options->getMultipartArray();

        if (isset($this->storedfiles['exlibrisfile'])) {
            $multipart[] = [
                'name'     => 'exlibrisfile',
                'contents' => $this->storedfiles['exlibrisfile'],
            ];
        }

        if (isset($this->files['epubfile'])) {
            $multipart[] = $this->files['epubfile'];
        }

        if (isset($this->files['exlibrisfile'])) {
            $multipart[] = $this->files['exlibrisfile'];
        }

        return $multipart;
    }
}
