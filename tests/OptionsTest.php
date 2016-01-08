<?php

namespace Icontact\BooXtreamClient\Tests;


use Icontact\BooXtreamClient\Options;

class OptionsTest extends \PHPUnit_Framework_TestCase
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

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage referenceid is not set or empty
     */
    public function testParseOptionsWithInvalidReferenceID() {
        $options = $this->validoptions;
        unset($options['referenceid']);

        $options = new Options($options);
        $options->parseOptions(true);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage at least one of customername or customeremailaddress is not set or empty
     */
    public function testParseOptionsWithInvalidCustomerNameAndEmailAddress() {
        $options = $this->validoptions;
        unset($options['customername']);
        unset($options['customeremailaddress']);

        $options = new Options($options);
        $options->parseOptions(true);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage languagecode is not set or empty
     */
    public function testParseOptionsWithInvalidLanguageCode() {
        $options = $this->validoptions;
        unset($options['languagecode']);

        $options = new Options($options);
        $options->parseOptions(true);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage downloadlimit is not set or not an integer
     */
    public function testParseOptionsWithInvalidDownloadLimit() {
        $options = $this->validoptions;
        $options['downloadlimit'] = 'str';

        $options = new Options($options);
        $options->parseOptions(true);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage expirydays is not set or not an integer
     */
    public function testParseOptionsWithInvalidExpiryDays() {
        $options = $this->validoptions;
        $options['expirydays'] = 'str';

        $options = new Options($options);
        $options->parseOptions(true);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage epub is not set or not a boolean
     */
    public function testParseOptionsWithInvalidEpub() {
        $options = $this->validoptions;
        $options['epub'] = 'str';

        $options = new Options($options);
        $options->parseOptions(true);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage kf8mobi is not set or not a boolean
     */
    public function testParseOptionsWithInvalidKF8Mobi() {
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
