<?php

namespace LizardsAndPumpkins\Website;

use LizardsAndPumpkins\Country\Country;

/**
 * @covers \LizardsAndPumpkins\Website\TwentyOneRunWebsiteToCountryMap
 * @uses   \LizardsAndPumpkins\Website\Website
 * @uses   \LizardsAndPumpkins\Country\Country
 */
class TwentyOneRunWebsiteToCountryMapTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TwentyOneRunWebsiteToCountryMap
     */
    private $websiteToCountryMap;

    private function assertCountryEqual(Country $expected, Country $actual)
    {
        $message = sprintf('Expected country "%s", got "%s"', $expected, $actual);
        $this->assertTrue($actual->isEqualTo($expected), $message);
    }
    
    protected function setUp()
    {
        $this->websiteToCountryMap = new TwentyOneRunWebsiteToCountryMap();
    }

    public function testItReturnsTheDefaultCountry()
    {
        $defaultCountry = $this->websiteToCountryMap->getDefaultCountry();
        $this->assertCountryEqual(Country::from2CharIso3166('DE'), $defaultCountry);
    }

    public function testItReturnsGermanyAsTheDefault()
    {
        
        $this->assertCountryEqual(
            $this->websiteToCountryMap->getDefaultCountry(),
            $this->websiteToCountryMap->getCountry(Website::fromString('unknown website'))
        );
    }

    /**
     * @dataProvider websiteToCountryDataProvider
     * @param Website $website
     * @param Country $expectedCountry
     */
    public function testItReturnsTheCountryForAGivenWebsite(Website $website, Country $expectedCountry)
    {
        $this->assertCountryEqual($expectedCountry, $this->websiteToCountryMap->getCountry($website));
    }

    /**
     * @return array[]
     */
    public function websiteToCountryDataProvider()
    {
        return [
            [Website::fromString('ru'), Country::from2CharIso3166('DE')],
            [Website::fromString('fr'), Country::from2CharIso3166('FR')],
            [Website::fromString('cy'), Country::from2CharIso3166('DE')],
        ];
    }
}
