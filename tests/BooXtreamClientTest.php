<?php

namespace Icontact\BooXtreamClient\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Icontact\BooXtreamClient\BooXtreamClient;

class BooXtreamClientTest extends \PHPUnit_Framework_TestCase
{
    protected $mocks = [];
    protected $basicobject;

    protected function setUp()
    {
        $this->mocks['guzzle']  = $this->getMock('GuzzleHttp\Client');
        $this->mocks['options'] = $this->getMockBuilder('Icontact\BooXtreamClient\Options')
                                       ->disableOriginalConstructor()
                                       ->getMock();
        $this->basicobject      = new BooXtreamClient('epub', $this->mocks['options'], [], $this->mocks['guzzle']);
    }

    public function testSetExistingEpubFile()
    {
        $this->assertTrue($this->basicobject->setEpubFile('./examples/assets/test.epub'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetNonExistingEpubFile()
    {
        $this->basicobject->setEpubFile('../nope');
    }

    public function testSetExistingExlibrisFile()
    {
        $this->assertTrue($this->basicobject->setExlibrisFile('./examples/assets/customexlibris.png'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetNonExistingExlibrisFile()
    {
        $this->basicobject->setExlibrisFile('../nope');
    }

    public function testSetExistingStoredEpubFile()
    {
        $mock    = new MockHandler([
            new Response(200)
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);
        $this->assertTrue($bx->setStoredEpubFile('test.epub'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetNonExistingStoredEpubFile()
    {
        $mock    = new MockHandler([
            new Response(404)
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);
        $bx->setStoredEpubFile('test');
    }

    public function testSetExistingStoredExlibrisFile()
    {
        $mock    = new MockHandler([
            new Response(200)
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);
        $this->assertTrue($bx->setStoredExlibrisFile('test'));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetNonExistingStoredExlibrisFile()
    {
        $mock    = new MockHandler([
            new Response(404)
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);
        $bx->setStoredEpubFile('test');
    }

    /**
     * @expectedException \GuzzleHttp\Exception\ClientException
     */
    public function testIncorrectCredentials()
    {
        $mock    = new MockHandler([
            new Response(401)
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);
        $bx->setStoredEpubFile('test');
    }
}
