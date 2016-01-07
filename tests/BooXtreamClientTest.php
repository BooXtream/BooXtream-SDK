<?php

namespace Icontact\BooXtreamClient\Tests;

use Icontact\BooXtreamClient\BooXtreamClient;

class BooXtreamClientTest extends \PHPUnit_Framework_TestCase
{
    protected $basicobject;

    protected function setUp() {
        $guzzle = $this->getMock('GuzzleHttp\Client');
        $options = $this->getMockBuilder('Icontact\BooXtreamClient\Options')
                        ->disableOriginalConstructor()
                        ->getMock();

        $this->basicobject = new BooXtreamClient('epub', $options, [], $guzzle);
    }

    public function testSetExistingEpubFile()
    {
        $this->assertTrue($this->basicobject->setEpubFile('./examples/assets/test.epub'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetNonExistingEpubFile() {
        $this->basicobject->setEpubFile('../nope');
    }

    public function testSetExistingExlibrisFile()
    {
        $this->assertTrue($this->basicobject->setExlibrisFile('./examples/assets/customexlibris.png'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetNonExistingExlibrisFile() {
        $this->basicobject->setExlibrisFile('../nope');
    }
-
}
