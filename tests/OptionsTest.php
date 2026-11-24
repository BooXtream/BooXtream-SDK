<?php

declare(strict_types=1);

namespace Icontact\BooXtreamClient\Tests;

use Icontact\BooXtreamClient\Options;
use PHPUnit\Framework\TestCase;

final class OptionsTest extends TestCase
{
    private array $validoptions = [
        'referenceid'          => 'str',
        'customername'         => 'str',
        'customeremailaddress' => 'str',
        'languagecode'         => 1,
        'downloadlimit'        => 1,
        'expirydays'           => 1,
    ];

    public function testParseOptionsWithoutXMLType(): void
    {
        $options = new Options($this->validoptions);
        $this->expectNotToPerformAssertions();
        $options->parseOptions(false);
    }

    public function testParseOptionsWithXMLType(): void
    {
        $options = new Options($this->validoptions);
        $this->expectNotToPerformAssertions();
        $options->parseOptions(true);
    }

    public function testParseOptionsWithInvalidReferenceID(): void
    {
        $this->expectExceptionMessage('referenceid is not set or empty');
        $this->expectException(\InvalidArgumentException::class);
        $options = $this->validoptions;
        unset($options['referenceid']);

        $options = new Options($options);
        $options->parseOptions(true);
    }

    public function testParseOptionsWithInvalidCustomerNameAndEmailAddress(): void
    {
        $this->expectExceptionMessage('at least one of customername or customeremailaddress is not set or empty');
        $this->expectException(\InvalidArgumentException::class);
        $options = $this->validoptions;
        unset($options['customername']);
        unset($options['customeremailaddress']);

        $options = new Options($options);
        $options->parseOptions(true);
    }

    public function testParseOptionsWithInvalidLanguageCode(): void
    {
        $this->expectExceptionMessage('languagecode is not set or empty');
        $this->expectException(\InvalidArgumentException::class);
        $options = $this->validoptions;
        unset($options['languagecode']);

        $options = new Options($options);
        $options->parseOptions(true);
    }

    public function testParseOptionsWithInvalidDownloadLimit(): void
    {
        $this->expectExceptionMessage('downloadlimit is not set or not an integer');
        $this->expectException(\InvalidArgumentException::class);
        $options                  = $this->validoptions;
        $options['downloadlimit'] = 'str';

        $options = new Options($options);
        $options->parseOptions(true);
    }

    public function testParseOptionsWithInvalidExpiryDays(): void
    {
        $this->expectExceptionMessage('expirydays is not set or not an integer');
        $this->expectException(\InvalidArgumentException::class);
        $options               = $this->validoptions;
        $options['expirydays'] = 'str';

        $options = new Options($options);
        $options->parseOptions(true);
    }

    public function testXmlDefaultsFillGapsButNeverOverrideGivenValues(): void
    {
        $given = $this->validoptions;
        unset($given['expirydays']);
        $options = new Options(['downloadlimit' => 1] + $given);
        $options->parseOptions(true);
        $sent = array_column($options->getMultipartArray(), 'contents', 'name');
        $this->assertSame('1', $sent['downloadlimit']);
        $this->assertSame('30', $sent['expirydays']);
    }

    public function testEpubOptionIsNotSent(): void
    {
        $options = new Options($this->validoptions);
        $options->parseOptions(true);
        $this->assertNotContains('epub', array_column($options->getMultipartArray(), 'name'));
    }

    public function testGetMultipartArray(): void
    {
        $options = new Options($this->validoptions);
        $this->assertNotCount(0, $options->getMultipartArray());
    }
}
