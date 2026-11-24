<?php

declare(strict_types=1);

namespace Icontact\BooXtreamClient\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Icontact\BooXtreamClient\BooXtreamClient;
use Icontact\BooXtreamClient\Options;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

final class BooXtreamClientTest extends TestCase
{
    private const EPUB = __DIR__.'/../examples/assets/test.epub';

    private const PDF = __DIR__.'/../examples/assets/test.pdf';

    private const EXLIBRIS = __DIR__.'/../examples/assets/customexlibris.png';

    /** @var list<array{request: \Psr\Http\Message\RequestInterface}> */
    private array $history = [];

    private function client(int ...$statuses): BooXtreamClient
    {
        return $this->clientWith(null, ...$statuses);
    }

    private function clientWith(?Options $options, int ...$statuses): BooXtreamClient
    {
        $stack = HandlerStack::create(new MockHandler(array_map(fn (int $s) => new Response($s), $statuses)));
        $stack->push(Middleware::history($this->history));

        return new BooXtreamClient('direct', $options ?? $this->createStub(Options::class), [], new Client(['handler' => $stack]));
    }

    public function testSetInvalidType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new BooXtreamClient('bla', $this->createStub(Options::class), [], $this->createStub(ClientInterface::class));
    }

    public function testEpubTypeIsReplacedByDirect(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('invalid type epub');
        new BooXtreamClient('epub', $this->createStub(Options::class), [], $this->createStub(ClientInterface::class));
    }

    public function testMobiTypeIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('invalid type mobi');
        new BooXtreamClient('mobi', $this->createStub(Options::class), [], $this->createStub(ClientInterface::class));
    }

    public function testSetExistingFile(): void
    {
        $this->expectNotToPerformAssertions();
        $this->client()->setFile(self::EPUB);
    }

    public function testSetNonExistingFile(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->client()->setFile('../nope');
    }

    public function testSetExistingExlibrisFile(): void
    {
        $this->expectNotToPerformAssertions();
        $this->client()->setExlibrisFile(self::EXLIBRIS);
    }

    public function testSetNonExistingExlibrisFile(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->client()->setExlibrisFile('../nope');
    }

    public function testSetExistingStoredFile(): void
    {
        $this->client(200)->setStoredFile('test.epub');
        $this->assertSame('/storedfiles/test', $this->history[0]['request']->getUri()->getPath());
    }

    public function testSetNonExistingStoredFile(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('storedfile test does not exist');
        $this->client(404)->setStoredFile('test');
    }

    public function testSetExistingStoredExlibrisFile(): void
    {
        $this->client(200)->setStoredExlibrisFile('test.png');
        $this->assertSame('/storedfiles/test.png', $this->history[0]['request']->getUri()->getPath());
    }

    public function testSetNonExistingStoredExlibrisFile(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('storedfile test does not exist');
        $this->client(404)->setStoredExlibrisFile('test');
    }

    public function testSetStoredFileWithUnexpectedResponse(): void
    {
        $this->expectException(ClientException::class);
        $this->client(401)->setStoredFile('test');
    }

    public function testSetFileThenStoredFile(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('local file set but also trying to set stored file');
        $bx = $this->client(200);
        $bx->setFile(self::EPUB);
        $bx->setStoredFile('test.epub');
    }

    public function testSetStoredFileThenFile(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('stored file set but also trying to set local file');
        $bx = $this->client(200);
        $bx->setStoredFile('test.epub');
        $bx->setFile(self::EPUB);
    }

    public function testSetStoredExlibrisFileThenExlibrisFile(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('stored exlibrisfile set but also trying to set local exlibrisfile');
        $bx = $this->client(200);
        $bx->setStoredExlibrisFile('test');
        $bx->setExlibrisFile(self::EXLIBRIS);
    }

    public function testSetExlibrisFileThenStoredExlibrisFile(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('exlibrisfile set but also trying to set storedfile');
        $bx = $this->client(200);
        $bx->setExlibrisFile(self::EXLIBRIS);
        $bx->setStoredExlibrisFile('test');
    }

    public function testSendRequestWithStoredFile(): void
    {
        $bx = $this->client(200, 200);
        $bx->setStoredFile('My.Book.EPUB');
        $this->assertInstanceOf(ResponseInterface::class, $bx->send());
        $this->assertSame(
            'https://service.booxtream.com/storedfiles/My.Book.epub',
            (string) $this->history[1]['request']->getUri()
        );
    }

    public function testStoredFileOnlyStripsTrailingEpubExtension(): void
    {
        $this->client(200)->setStoredFile('book.epub.v2');
        $this->assertSame(
            'https://service.booxtream.com/storedfiles/book.epub.v2?exists=',
            (string) $this->history[0]['request']->getUri()
        );
    }

    public function testStoredFileNameIsUrlEncoded(): void
    {
        $bx = $this->client(200, 200);
        $bx->setStoredFile('boek#2.epub');
        $bx->send();
        $this->assertSame('/storedfiles/boek%232', $this->history[0]['request']->getUri()->getPath());
        $this->assertSame('/storedfiles/boek%232.epub', $this->history[1]['request']->getUri()->getPath());
    }

    public function testStoredFileInSubfolderKeepsItsSlashes(): void
    {
        $bx = $this->client(200, 200);
        $bx->setStoredFile('map/sub/boek.epub');
        $bx->send();
        $this->assertSame('/storedfiles/map/sub/boek', $this->history[0]['request']->getUri()->getPath());
        $this->assertSame('/storedfiles/map/sub/boek.epub', $this->history[1]['request']->getUri()->getPath());
    }

    /** @return list<array{string}> */
    public static function dotSegmentNames(): array
    {
        return [['../booxtream'], ['map/../../x'], ['./boek'], ['map//boek']];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dotSegmentNames')]
    public function testStoredFileWithDotOrEmptySegmentIsRejected(string $name): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('storedfile name contains an empty, "." or ".." segment');
        $this->client(200)->setStoredFile($name);
    }

    public function testEmptyStoredFileIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('storedfile name is empty');
        $this->client()->setStoredFile('.epub');
    }

    public function testEmptyStoredExlibrisFileIsRejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('storedfile name is empty');
        $this->client()->setStoredExlibrisFile('');
    }

    public function testSendRequestWithFile(): void
    {
        $bx = $this->client(200);
        $bx->setFile(self::EPUB);
        $this->assertInstanceOf(ResponseInterface::class, $bx->send());
        $this->assertSame('https://service.booxtream.com/booxtream.epub', (string) $this->history[0]['request']->getUri());
    }

    /** @return array<string, string> multipart field name => value of the last request */
    private function sentFields(): array
    {
        $body = (string) end($this->history)['request']->getBody();
        preg_match_all('/name="([^"]+)"(?:; filename="[^"]*")?\r\n(?:[^\r\n]+\r\n)*\r\n(.*?)\r\n--/s', $body, $m);

        return array_combine($m[1], $m[2]);
    }

    private function realOptions(): Options
    {
        return new Options(['customername' => 'x', 'referenceid' => '1', 'languagecode' => 1033]);
    }

    public function testDirectPdfGetsLinkDefaults(): void
    {
        $bx = $this->clientWith($this->realOptions(), 200);
        $bx->setFile(self::PDF);
        $bx->send();
        $fields = $this->sentFields();
        $this->assertSame('/booxtream.epub', end($this->history)['request']->getUri()->getPath());
        $this->assertSame('30', $fields['expirydays'] ?? null);
        $this->assertSame('3', $fields['downloadlimit'] ?? null);
        $this->assertArrayHasKey('epubfile', $fields);
    }

    public function testDirectStoredPdfGetsLinkDefaults(): void
    {
        $bx = $this->clientWith($this->realOptions(), 200, 200);
        $bx->setStoredFile('boek.pdf');
        $bx->send();
        $this->assertSame('/storedfiles/boek.pdf.epub', end($this->history)['request']->getUri()->getPath());
        $this->assertSame('30', $this->sentFields()['expirydays'] ?? null);
    }

    public function testDirectEpubGetsNoLinkDefaults(): void
    {
        $bx = $this->clientWith($this->realOptions(), 200);
        $bx->setFile(self::EPUB);
        $bx->send();
        $this->assertArrayNotHasKey('expirydays', $this->sentFields());
    }

    public function testSendRequestReturnOtherCode(): void
    {
        $this->expectException(ClientException::class);
        $bx = $this->client(200, 401);
        $bx->setStoredFile('test.epub');
        $bx->send();
    }

    public function testSendRequestWithoutFile(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->client()->send();
    }

    public function testSendRequestWithStoredExlibrisFile(): void
    {
        $bx = $this->client(200, 200, 200);
        $bx->setStoredFile('test.epub');
        $bx->setStoredExlibrisFile('exlibris.png');
        $this->assertInstanceOf(ResponseInterface::class, $bx->send());
    }

    public function testSendRequestWithExlibrisFile(): void
    {
        $bx = $this->client(200);
        $bx->setFile(self::EPUB);
        $bx->setExlibrisFile(self::EXLIBRIS);
        $this->assertInstanceOf(ResponseInterface::class, $bx->send());
    }
}
