<?php

namespace Icontact\BooXtreamClient\Tests;


use Icontact\BooXtreamClient\Options;
use PHPUnit\Framework\TestCase;

class OptionsTest extends TestCase
{
    protected $validoptions = [
        'referenceid'          => 'str',
        'customername'         => 'str',
        'customeremailaddress' => 'str',
        'languagecode'         => 1,
        'downloadlimit'        => 1,
        'expirydays'           => 1,
        'epub'                 => true,
        'kf8mobi'              => true
    ];

    public function testObject()
    {
        $this->assertInstanceOf('\Icontact\BooXtreamClient\Options', new Options([ ]));
    }

    public function testParseOptionsWithoutXMLType() {
        $options = new Options($this->validoptions);
        $this->assertTrue($options->parseOptions(false));
    }

    public function testParseOptionsWithXMLType() {
        $options = new Options($this->validoptions);
        $this->assertTrue($options->parseOptions(true));
    }

    public function testParseOptionsWithInvalidReferenceID() {
        $this->expectExceptionMessage("referenceid is not set or empty");
        $this->expectException(\InvalidArgumentException::class);
        $options = $this->validoptions;
        unset($options['referenceid']);

        $options = new Options($options);
        $options->parseOptions(true);
    }

    public function testParseOptionsWithInvalidCustomerNameAndEmailAddress() {
        $this->expectExceptionMessage("at least one of customername or customeremailaddress is not set or empty");
        $this->expectException(\InvalidArgumentException::class);
        $options = $this->validoptions;
        unset($options['customername']);
        unset($options['customeremailaddress']);

        $options = new Options($options);
        $options->parseOptions(true);
    }

    public function testParseOptionsWithInvalidLanguageCode() {
        $this->expectExceptionMessage("languagecode is not set or empty");
        $this->expectException(\InvalidArgumentException::class);
        $options = $this->validoptions;
        unset($options['languagecode']);

        $options = new Options($options);
        $options->parseOptions(true);
    }

    public function testParseOptionsWithInvalidDownloadLimit() {
        $this->expectExceptionMessage("downloadlimit is not set or not an integer");
        $this->expectException(\InvalidArgumentException::class);
        $options = $this->validoptions;
        $options['downloadlimit'] = 'str';

        $options = new Options($options);
        $options->parseOptions(true);
    }

    public function testParseOptionsWithInvalidExpiryDays() {
        $this->expectExceptionMessage("expirydays is not set or not an integer");
        $this->expectException(\InvalidArgumentException::class);
        $options = $this->validoptions;
        $options['expirydays'] = 'str';

        $options = new Options($options);
        $options->parseOptions(true);
    }

    public function testParseOptionsWithInvalidEpub() {
        $this->expectExceptionMessage("epub is not set or not a boolean");
        $this->expectException(\InvalidArgumentException::class);
        $options = $this->validoptions;
        $options['epub'] = 'str';

        $options = new Options($options);
        $options->parseOptions(true);
    }

    public function testParseOptionsWithInvalidKF8Mobi() {
        $this->expectExceptionMessage("kf8mobi is not set or not a boolean");
        $this->expectException(\InvalidArgumentException::class);
        $options = $this->validoptions;
        $options['kf8mobi'] = 'str';

        $options = new Options($options);
        $options->parseOptions(true);
    }

    public function testGetMultipartArray() {
        $options = new Options($this->validoptions);
        $this->assertNotCount(0, $options->getMultipartArray());
    }
}
