<?php

declare(strict_types=1);

namespace Icontact\BooXtreamClient;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\ResponseInterface;

/**
 * Use to connect to and use the BooXtream webservice.
 */
class BooXtreamClient implements BooXtreamClientInterface
{
    public const BASE_URL = 'https://service.booxtream.com';

    // type => service endpoint; the service picks EPUB or PDF from the file extension
    private const TYPES = ['xml' => 'xml', 'direct' => 'epub'];

    /** @var array<string, array{name: string, filename: string, contents: resource}> */
    private array $files = [];

    /** @var array<string, string> */
    private array $storedfiles = [];

    /**
     * @param  array{0: string, 1: string}  $authentication  [username, apikey]
     */
    public function __construct(
        private readonly string $type,
        private readonly Options $options,
        private readonly array $authentication,
        private readonly ClientInterface $guzzle,
    ) {
        if (! isset(self::TYPES[$type])) {
            throw new \InvalidArgumentException('invalid type '.$type);
        }

        $this->options->parseOptions($this->type === 'xml');
    }

    public function setFile(string $file): void
    {
        if (isset($this->storedfiles['epubfile'])) {
            throw new \RuntimeException('stored file set but also trying to set local file');
        }
        $this->files['epubfile'] = $this->checkFile('epubfile', $file);
    }

    public function setExlibrisFile(string $file): void
    {
        if (isset($this->storedfiles['exlibrisfile'])) {
            throw new \RuntimeException('stored exlibrisfile set but also trying to set local exlibrisfile');
        }
        $this->files['exlibrisfile'] = $this->checkFile('exlibrisfile', $file);
    }

    public function setStoredFile(string $storedfile): void
    {
        if (isset($this->files['epubfile'])) {
            throw new \RuntimeException('local file set but also trying to set stored file');
        }

        if (str_ends_with(strtolower($storedfile), '.epub')) {
            $storedfile = substr($storedfile, 0, -5);
        }
        $this->storedfiles['epubfile'] = $this->checkStoredFile($storedfile);
    }

    public function setStoredExlibrisFile(string $storedfile): void
    {
        if (isset($this->files['exlibrisfile'])) {
            throw new \RuntimeException('exlibrisfile set but also trying to set storedfile');
        }
        $this->storedfiles['exlibrisfile'] = $this->checkStoredFile($storedfile);
    }

    public function send(): ResponseInterface
    {
        if (! isset($this->storedfiles['epubfile']) && ! isset($this->files['epubfile'])) {
            throw new \RuntimeException('file or stored file not set');
        }

        $name = $this->storedfiles['epubfile'] ?? $this->files['epubfile']['filename'];
        if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) === 'pdf') {
            // the core API requires expirydays and downloadlimit for every PDF, also for a direct download
            $this->options->parseOptions(true);
        }

        $endpoint = self::TYPES[$this->type];
        $action   = isset($this->storedfiles['epubfile'])
            ? self::storedFileUrl($this->storedfiles['epubfile']).'.'.$endpoint
            : self::BASE_URL.'/booxtream.'.$endpoint;

        return $this->guzzle->request('POST', $action, [
            'auth'      => $this->authentication,
            'multipart' => $this->getMultipart(),
        ]);
    }

    /**
     * @return array{name: string, filename: string, contents: resource}
     */
    private function checkFile(string $name, string $file): array
    {
        if (! is_file($file) || ! is_readable($file)) {
            throw new \InvalidArgumentException('file '.$file.' not found or readable while setting '.$name);
        }

        return [
            'name'     => $name,
            'filename' => basename($file),
            'contents' => fopen($file, 'r'),
        ];
    }

    private function checkStoredFile(string $storedfile): string
    {
        if ($storedfile === '') {
            throw new \InvalidArgumentException('storedfile name is empty');
        }
        if (array_intersect(explode('/', $storedfile), ['', '.', '..'])) {
            throw new \InvalidArgumentException('storedfile name contains an empty, "." or ".." segment');
        }

        try {
            $this->guzzle->request('GET', self::storedFileUrl($storedfile), [
                'auth'  => $this->authentication,
                'query' => ['exists' => ''],
            ]);
        } catch (ClientException $e) {
            if ($e->getCode() === 404) {
                throw new \InvalidArgumentException('storedfile '.$storedfile.' does not exist', 0, $e);
            }
            throw $e;
        }

        return $storedfile;
    }

    // encode per segment so a path like map/sub/book keeps its slashes; Apache 404s on %2F by default
    private static function storedFileUrl(string $storedfile): string
    {
        return self::BASE_URL.'/storedfiles/'.implode('/', array_map(rawurlencode(...), explode('/', $storedfile)));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function getMultipart(): array
    {
        $multipart = $this->options->getMultipartArray();

        if (isset($this->storedfiles['exlibrisfile'])) {
            $multipart[] = ['name' => 'exlibrisfile', 'contents' => $this->storedfiles['exlibrisfile']];
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
