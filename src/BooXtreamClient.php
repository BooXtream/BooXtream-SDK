<?php
namespace Icontact\BooXtream;

use \GuzzleHttp\ClientInterface;

/**
 * Class BooXtreamClient
 * Use to connect to and use the BooXtream webservice
 */
class BooXtreamClient implements BooXtreamInterface {
    private $guzzle;
    private $username;
    private $apikey;

    public function __construct(ClientInterface $guzzle, $username, $apikey) {
        $this->guzzle = $guzzle;
        $this->username = $username;
        $this->apikey = $apikey;
    }

    public function createRequest($method, array $options = []) {
        $options = array_replace_recursive(getDefaultOptions(), $options);
        var_dump($options);
    }

    private function getDefaultOptions() {
        $options = [
            'exlibris'          => false,
            'chapterfooter'     => false,
            'disclaimer'        => false
        ];

        if($this->method === XML) {
            $options = array_merge($options, [
                'expirydays'    => 30;
                'downloadlimit' => 3,
                'epub'          => true,
                'kf8mobi'       => false
            ]);
        }

        return $options;
    }
}