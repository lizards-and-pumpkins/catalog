<?php

namespace LizardsAndPumpkins\Website;

use LizardsAndPumpkins\Website\Exception\InvalidWebsiteCodeException;

/**
 * @covers \LizardsAndPumpkins\Website\TwentyOneRunWebsiteToCountryMap
 */
class TwentyOneRunWebsiteToCountryMapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TwentyOneRunWebsiteToCountryMap
     */
    private $websiteToCountryMap;

    protected function setUp()
    {
        $this->websiteToCountryMap = new TwentyOneRunWebsiteToCountryMap();
    }

    /**
     * @param mixed $invalidWebsiteCode
     * @param string $expectedType
     * @dataProvider invalidWebsiteCodeProvider
     */
    public function testItThrowsAnExceptionIfTheWebsiteCodeIsNotAString($invalidWebsiteCode, $expectedType)
    {
        $this->setExpectedException(
            InvalidWebsiteCodeException::class,
            'The website code must be a string, got "' . $expectedType . '"'
        );
        $this->websiteToCountryMap->getCountry($invalidWebsiteCode);
    }

    /**
     * @return array[]
     */
    public function invalidWebsiteCodeProvider()
    {
        return [
            [123, 'integer'],
            [[], 'array'],
            [$this, get_class($this)]
        ];
    }

    public function testItThrowsAnExceptionIfTheWebsiteCodeIsEmpty()
    {
        $this->setExpectedException(
            InvalidWebsiteCodeException::class,
            'The website code can not be an empty string'
        );
        $this->websiteToCountryMap->getCountry(' ');
    }

    public function testItReturnsTheDefaultCountry()
    {
        $this->assertSame('DE', $this->websiteToCountryMap->getDefaultCountry());
    }

    public function testItReturnsGermanyAsTheDefault()
    {
        $this->assertSame(
            $this->websiteToCountryMap->getDefaultCountry(),
            $this->websiteToCountryMap->getCountry('undefined website')
        );
    }

    /**
     * @dataProvider websiteToCountryDataProvider
     * @param string $websiteCode
     * @param string $expectedCountry
     */
    public function testItReturnsTheCountryForAGivenWebsiteCode($websiteCode, $expectedCountry)
    {
        $this->assertSame($expectedCountry, $this->websiteToCountryMap->getCountry($websiteCode));
    }

    /**
     * @return array[]
     */
    public function websiteToCountryDataProvider()
    {
        return [
            ['ru', 'DE'],
            ['fr', 'FR'],
            ['cy', 'DE'],
        ];
    }
}
