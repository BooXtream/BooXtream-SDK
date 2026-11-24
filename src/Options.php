<?php

declare(strict_types=1);

namespace Icontact\BooXtreamClient;

class Options
{
    private const DEFAULTS = [
        'exlibris'      => false,
        'chapterfooter' => false,
        'disclaimer'    => false,
        'showdate'      => false,
    ];

    private const XML_DEFAULTS = [
        'expirydays'    => 30,
        'downloadlimit' => 3,
    ];

    /** @var array<string, mixed> */
    private array $options;

    /**
     * @param  array<string, mixed>  $options
     */
    public function __construct(array $options)
    {
        $this->options = array_replace(self::DEFAULTS, $options);
    }

    public function parseOptions(bool $linkDefaults): void
    {
        if (empty($this->options['customername']) && empty($this->options['customeremailaddress'])) {
            throw new \InvalidArgumentException('at least one of customername or customeremailaddress is not set or empty');
        }
        if (empty($this->options['referenceid'])) {
            throw new \InvalidArgumentException('referenceid is not set or empty');
        }
        if (empty($this->options['languagecode'])) {
            throw new \InvalidArgumentException('languagecode is not set or empty');
        }

        if ($linkDefaults) {
            $this->options = array_replace(self::XML_DEFAULTS, $this->options);

            foreach (['expirydays', 'downloadlimit'] as $name) {
                if (! is_int($this->options[$name])) {
                    throw new \InvalidArgumentException($name.' is not set or not an integer');
                }
            }
        }
    }

    /**
     * @return list<array{name: string, contents: string}>
     */
    public function getMultipartArray(): array
    {
        $multipart = [];
        foreach ($this->options as $name => $contents) {
            $multipart[] = ['name' => $name, 'contents' => (string) $contents];
        }

        return $multipart;
    }
}
