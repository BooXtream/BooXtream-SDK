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

    public function testObject()
    {
        $this->assertInstanceOf('\Icontact\BooXtreamClient\BooXtreamClient', $this->basicobject);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetInvalidType()
    {
        new BooXtreamClient('bla', $this->mocks['options'], [], $this->mocks['guzzle']);
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
     * @expectedExceptionMessage storedfile test does not exist
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
    public function testSetStoredEpubFileWithUnexpectedResponse()
    {
        $mock    = new MockHandler([
            new Response(401)
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);
        $bx->setStoredEpubFile('test');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage epubfile set but also trying to set storedfile
     */
    public function testSetEpubFileThenStoredEpubFile()
    {
        $mock    = new MockHandler([
            new Response(200)
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);

        $bx->setEpubFile('./examples/assets/test.epub');
        $bx->setStoredEpubFile('test.epub');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage stored epubfile set but also trying to set local epubfile
     */
    public function testSetStoredEpubFileThenEpubFile()
    {
        $mock    = new MockHandler([
            new Response(200)
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);

        $bx->setStoredEpubFile('test.epub');
        $bx->setEpubFile('./examples/assets/test.epub');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage stored exlibrisfile set but also trying to set local exlibrisfile
     */
    public function testSetStoredExlibrisFileThenExlibrisFile()
    {
        $mock    = new MockHandler([
            new Response(200)
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);

        $bx->setStoredExlibrisFile('test');
        $bx->setExlibrisFile('./examples/assets/customexlibris.png');
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage exlibrisfile set but also trying to set storedfile
     */
    public function testSetExlibrisFileThenStoredExlibrisFile()
    {
        $mock    = new MockHandler([
            new Response(200)
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);

        $bx->setExlibrisFile('./examples/assets/customexlibris.png');
        $bx->setStoredExlibrisFile('test');
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

    public function testSendRequestWithStoredEpubFile()
    {
        $mock    = new MockHandler([
            new Response(200),
            new Response(200)
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);
        $bx->setStoredEpubFile('test.epub');
        $this->assertInstanceOf('\Psr\Http\Message\ResponseInterface', $bx->send());
    }

    public function testSendRequestWithEpubFile()
    {
        $mock    = new MockHandler([
            new Response(200)
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);
        $bx->setEpubFile('./examples/assets/test.epub');
        $this->assertInstanceOf('\Psr\Http\Message\ResponseInterface', $bx->send());
    }

    /**
     * @expectedException \GuzzleHttp\Exception\ClientException
     */
    public function testSendRequestReturnOtherCode()
    {
        $mock    = new MockHandler([
            new Response(200),
            new Response(401)
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);
        $bx->setStoredEpubFile('test.epub');
        $bx->send();
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSendRequestWithoutEpub()
    {
        $mock    = new MockHandler([
            new Response(200),
            new Response(404)
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);
        $bx->send();
    }

    public function testSendRequestWithStoredExlibrisFile()
    {
        $mock    = new MockHandler([
            new Response(200),
            new Response(200),
            new Response(200)
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);
        $bx->setStoredEpubFile('test.epub');
        $bx->setStoredExlibrisFile('exlibris.png');
        $this->assertInstanceOf('\Psr\Http\Message\ResponseInterface', $bx->send());
    }

    public function testSendRequestWithExlibrisFile()
    {
        $mock    = new MockHandler([
            new Response(200)
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);
        $bx->setEpubFile('./examples/assets/test.epub');
        $bx->setExlibrisFile('./examples/assets/customexlibris.png');
        $this->assertInstanceOf('\Psr\Http\Message\ResponseInterface', $bx->send());
    }
}
