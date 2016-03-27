<?php

namespace LizardsAndPumpkins\Context\Country;

use LizardsAndPumpkins\Context\Country\Country;
use LizardsAndPumpkins\Context\Country\Exception\InvalidCountrySpecificationException;

/**
 * @covers \LizardsAndPumpkins\Context\Country\Country
 */
class CountryTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsACountryInstance()
    {
        $this->assertInstanceOf(Country::class, Country::from2CharIso3166('de'));
    }

    /**
     * @param mixed $nonString
     * @dataProvider nonStringDataProvider
     */
    public function testItThrowsAnExceptionIfTheInputIsNotAString($nonString)
    {
        $this->expectException(InvalidCountrySpecificationException::class);
        $this->expectExceptionMessage('The country specification has to be a string, got "');
        Country::from2CharIso3166($nonString);
    }

    /**
     * @return array[]
     */
    public function nonStringDataProvider()
    {
        return [
            [null],
            [0],
        ];
    }

    /**
     * @param string $invalidCountrySpec
     * @dataProvider invalidCountrySpecStringProvider
     */
    public function testItThrowsAnExceptionIfTheInputStringIsNotTwoCharactersLong($invalidCountrySpec)
    {
        $this->expectException(InvalidCountrySpecificationException::class);
        $this->expectExceptionMessage('Two character string country specification expected (ISO 3166), got "');
        Country::from2CharIso3166($invalidCountrySpec);
    }

    /**
     * @return array[]
     */
    public function invalidCountrySpecStringProvider()
    {
        return [
            ['x'],
            ['xxx'],
            ['x '],
        ];
    }

    /**
     * @param string $emptyStringProvider
     * @dataProvider emptyStringDataProvider
     */
    public function testItThrowsAnExceptionIfTheInputStringIsEmpty($emptyStringProvider)
    {
        $this->expectException(InvalidCountrySpecificationException::class);
        $this->expectExceptionMessage('The country specification must not be empty');
        Country::from2CharIso3166($emptyStringProvider);
    }

    /**
     * @return array[]
     */
    public function emptyStringDataProvider()
    {
        return [
            [''],
            [' '],
        ];
    }

    /**
     * @param string $outOfBoundsCountrySpec
     * @dataProvider countrySpecWithInvalidCharactersProvider
     */
    public function testItThrowsAnExceptionIfItContainsCharactersBeyondAToZ($outOfBoundsCountrySpec)
    {
        $this->expectException(InvalidCountrySpecificationException::class);
        $this->expectExceptionMessage(
            'The country specification may only contain characters from a-z, got "' . $outOfBoundsCountrySpec . '"'
        );
        Country::from2CharIso3166($outOfBoundsCountrySpec);
    }

    /**
     * @return array[]
     */
    public function countrySpecWithInvalidCharactersProvider()
    {
        return [
            ['e1'],
            ['!e'],
        ];
    }

    public function testItAcceptsACountryInstanceAsValidInput()
    {
        $country = Country::from2CharIso3166('it');
        $this->assertSame($country, Country::from2CharIso3166($country));
    }

    public function testItReturnsTheCountryCodeString()
    {
        $this->assertSame('DE', (string) Country::from2CharIso3166('DE'));
    }

    public function testItReturnsTheInputStringInUpperCase()
    {
        $this->assertSame('EN', (string) Country::from2CharIso3166('en'));
    }

    public function testCountriesWithDifferentCodeAreNotEqual()
    {
        $countryDE = Country::from2CharIso3166('de');
        $countryEN = Country::from2CharIso3166('en');
        $this->assertFalse($countryDE->isEqualTo($countryEN));
    }

    public function testCountriesWithTheSameCodeAreEqual()
    {
        $country1 = Country::from2CharIso3166('de');
        $country2 = Country::from2CharIso3166('DE');
        $this->assertTrue($country1->isEqualTo($country2));
    }
}
