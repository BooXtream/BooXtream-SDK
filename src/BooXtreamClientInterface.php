<?php

declare(strict_types=1);

namespace Icontact\BooXtreamClient;

use Psr\Http\Message\ResponseInterface;

/**
 * Use to connect to and use the BooXtream webservice.
 */
interface BooXtreamClientInterface
{
    public function setFile(string $file): void;

    public function setStoredFile(string $storedfile): void;

    public function setExlibrisFile(string $file): void;

    public function setStoredExlibrisFile(string $storedfile): void;

    public function send(): ResponseInterface;
}
