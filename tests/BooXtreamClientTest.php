<?php

namespace Icontact\BooXtreamClient\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Icontact\BooXtreamClient\BooXtreamClient;
use PHPUnit\Framework\TestCase;

class BooXtreamClientTest extends TestCase
{
    protected $mocks = [];

    protected $basicobject;

    public function testObject()
    {
        $this->assertInstanceOf('\Icontact\BooXtreamClient\BooXtreamClient', $this->basicobject);
    }

    public function testSetInvalidType()
    {
        $this->expectException(\InvalidArgumentException::class);
        new BooXtreamClient('bla', $this->mocks['options'], [], $this->mocks['guzzle']);
    }

    public function testSetExistingEpubFile()
    {
        $this->assertTrue($this->basicobject->setEpubFile('./examples/assets/test.epub'));
    }

    public function testSetNonExistingEpubFile()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->basicobject->setEpubFile('../nope');
    }

    public function testSetExistingExlibrisFile()
    {
        $this->assertTrue($this->basicobject->setExlibrisFile('./examples/assets/customexlibris.png'));
    }

    public function testSetNonExistingExlibrisFile()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->basicobject->setExlibrisFile('../nope');
    }

    public function testSetExistingStoredEpubFile()
    {
        $mock = new MockHandler([
            new Response(200),
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);
        $this->assertTrue($bx->setStoredEpubFile('test.epub'));
    }

    public function testSetNonExistingStoredEpubFile()
    {
        $this->expectException(\InvalidArgumentException::class);
        $mock = new MockHandler([
            new Response(404),
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);
        $bx->setStoredEpubFile('test');
    }

    public function testSetExistingStoredExlibrisFile()
    {
        $mock = new MockHandler([
            new Response(200),
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);
        $this->assertTrue($bx->setStoredExlibrisFile('test'));
    }

    public function testSetNonExistingStoredExlibrisFile()
    {
        $this->expectExceptionMessage('storedfile test does not exist');
        $this->expectException(\InvalidArgumentException::class);
        $mock = new MockHandler([
            new Response(404),
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);
        $bx->setStoredEpubFile('test');
    }

    public function testSetStoredEpubFileWithUnexpectedResponse()
    {
        $this->expectException(\GuzzleHttp\Exception\ClientException::class);
        $mock = new MockHandler([
            new Response(401),
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);
        $bx->setStoredEpubFile('test');
    }

    public function testSetEpubFileThenStoredEpubFile()
    {
        $this->expectExceptionMessage('epubfile set but also trying to set storedfile');
        $this->expectException(\RuntimeException::class);
        $mock = new MockHandler([
            new Response(200),
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);

        $bx->setEpubFile('./examples/assets/test.epub');
        $bx->setStoredEpubFile('test.epub');
    }

    public function testSetStoredEpubFileThenEpubFile()
    {
        $this->expectExceptionMessage('stored epubfile set but also trying to set local epubfile');
        $this->expectException(\RuntimeException::class);
        $mock = new MockHandler([
            new Response(200),
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);

        $bx->setStoredEpubFile('test.epub');
        $bx->setEpubFile('./examples/assets/test.epub');
    }

    public function testSetStoredExlibrisFileThenExlibrisFile()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('stored exlibrisfile set but also trying to set local exlibrisfile');
        $mock = new MockHandler([
            new Response(200),
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);

        $bx->setStoredExlibrisFile('test');
        $bx->setExlibrisFile('./examples/assets/customexlibris.png');
    }

    public function testSetExlibrisFileThenStoredExlibrisFile()
    {
        $this->expectExceptionMessage('exlibrisfile set but also trying to set storedfile');
        $this->expectException(\RuntimeException::class);
        $mock = new MockHandler([
            new Response(200),
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);

        $bx->setExlibrisFile('./examples/assets/customexlibris.png');
        $bx->setStoredExlibrisFile('test');
    }

    public function testIncorrectCredentials()
    {
        $this->expectException(\GuzzleHttp\Exception\ClientException::class);
        $mock = new MockHandler([
            new Response(401),
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);
        $bx->setStoredEpubFile('test');
    }

    public function testSendRequestWithStoredEpubFile()
    {
        $mock = new MockHandler([
            new Response(200),
            new Response(200),
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);
        $bx->setStoredEpubFile('test.epub');
        $this->assertInstanceOf('\Psr\Http\Message\ResponseInterface', $bx->send());
    }

    public function testSendRequestWithEpubFile()
    {
        $mock = new MockHandler([
            new Response(200),
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);
        $bx->setEpubFile('./examples/assets/test.epub');
        $this->assertInstanceOf('\Psr\Http\Message\ResponseInterface', $bx->send());
    }

    public function testSendRequestReturnOtherCode()
    {
        $this->expectException(\GuzzleHttp\Exception\ClientException::class);
        $mock = new MockHandler([
            new Response(200),
            new Response(401),
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);
        $bx->setStoredEpubFile('test.epub');
        $bx->send();
    }

    public function testSendRequestWithoutEpub()
    {
        $this->expectException(\RuntimeException::class);
        $mock = new MockHandler([
            new Response(200),
            new Response(404),
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);
        $bx->send();
    }

    public function testSendRequestWithStoredExlibrisFile()
    {
        $mock = new MockHandler([
            new Response(200),
            new Response(200),
            new Response(200),
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
        $mock = new MockHandler([
            new Response(200),
        ]);
        $handler = HandlerStack::create($mock);
        $guzzle  = new Client(['handler' => $handler]);
        $bx      = new BooXtreamClient('epub', $this->mocks['options'], [], $guzzle);
        $bx->setEpubFile('./examples/assets/test.epub');
        $bx->setExlibrisFile('./examples/assets/customexlibris.png');
        $this->assertInstanceOf('\Psr\Http\Message\ResponseInterface', $bx->send());
    }

    protected function setUp(): void
    {
        $this->mocks['guzzle']  = $this->createMock('GuzzleHttp\Client');
        $this->mocks['options'] = $this->getMockBuilder('Icontact\BooXtreamClient\Options')
                                       ->disableOriginalConstructor()
                                       ->getMock();
        $this->basicobject = new BooXtreamClient('epub', $this->mocks['options'], [], $this->mocks['guzzle']);
    }
}
