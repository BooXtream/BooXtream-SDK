<?php

namespace Icontact\BooXtreamClient;

/**
 * Class Options
 * @package Icontact\BooXtreamClient
 */
class Options
{
    /**
     * @var array
     */
    private $defaultoptions = [
        'exlibris'      => false,
        'chapterfooter' => false,
        'disclaimer'    => false,
        'showdate'      => false,
    ];

    /**
     * @var array
     */
    private $options;

    /**
     * Options constructor.
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->options = array_replace_recursive($this->defaultoptions, $options);
    }

    /**
     * @param $xml
     *
     * @return bool
     */
    public function parseOptions($xml)
    {
        try {
            // Required stuff
            $this->checkRequiredOptions();

            // Check additional options for XML requests
            if ($xml) {
                $this->options = array_replace_recursive(
                    [
                        'expirydays'    => 30,
                        'downloadlimit' => 3,
                        'epub'          => true,
                        'kf8mobi'       => false,
                    ],
                    $this->options
                );
                $this->checkXMLOptions();
            }
        } catch (\InvalidArgumentException $e) {
            throw $e;
        }
        return true;
    }

    /**
     * @return array
     */
    public function getMultipartArray()
    {
        $multipart = [];
        foreach ($this->options as $name => $contents) {
            $multipart[] = [
                'name'     => $name,
                'contents' => (string)$contents,
            ];
        }

        return $multipart;
    }

    /**
     * @return void
     */
    private function checkRequiredOptions()
    {
        if ( ! $this->checkString('customername') && ! $this->checkString('customeremailaddress')) {
            throw new \InvalidArgumentException('at least one of customername or customeremailaddress is not set or empty');
        }
        if ( ! $this->checkString('referenceid')) {
            throw new \InvalidArgumentException('referenceid is not set or empty');
        }
        if ( ! $this->checkString('languagecode')) {
            throw new \InvalidArgumentException('languagecode is not set or empty');
        }
    }

    /**
     * @return void
     */
    private function checkXMLOptions()
    {
        if ( ! $this->checkInt('expirydays')) {
            throw new \InvalidArgumentException('expirydays is not set or not an integer');
        }
        if ( ! $this->checkInt('downloadlimit')) {
            throw new \InvalidArgumentException('downloadlimit is not set or not an integer');
        }
        if ( ! $this->checkBool('epub')) {
            throw new \InvalidArgumentException('epub is not set or not a boolean');
        }
        if ( ! $this->checkBool('kf8mobi')) {
            throw new \InvalidArgumentException('kf8mobi is not set or not a boolean');
        }
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    private function checkString($name)
    {
        return isset($this->options[$name]) && ! empty($this->options[$name]);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    private function checkInt($name)
    {
        return isset($this->options[$name]) && is_int($this->options[$name]);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    private function checkBool($name)
    {
        return isset($this->options[$name]) && is_bool($this->options[$name]);
    }
}
