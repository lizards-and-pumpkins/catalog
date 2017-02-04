<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\Country;

use LizardsAndPumpkins\Context\Country\Exception\InvalidCountrySpecificationException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Context\Country\Country
 */
class CountryTest extends TestCase
{
    public function testReturnsACountryInstance()
    {
        $this->assertInstanceOf(Country::class, Country::from2CharIso3166('de'));
    }

    public function testItThrowsAnExceptionIfTheInputIsNotAString()
    {
        $this->expectException(\TypeError::class);
        Country::from2CharIso3166(new \stdClass());
    }

    /**
     * @dataProvider invalidCountrySpecStringProvider
     */
    public function testItThrowsAnExceptionIfTheInputStringIsNotTwoCharactersLong(string $invalidCountrySpec)
    {
        $this->expectException(InvalidCountrySpecificationException::class);
        $this->expectExceptionMessage('Two character string country specification expected (ISO 3166), got "');
        Country::from2CharIso3166($invalidCountrySpec);
    }

    /**
     * @return array[]
     */
    public function invalidCountrySpecStringProvider() : array
    {
        return [
            ['x'],
            ['xxx'],
            ['x '],
        ];
    }

    /**
     * @dataProvider emptyStringDataProvider
     */
    public function testItThrowsAnExceptionIfTheInputStringIsEmpty(string $emptyStringProvider)
    {
        $this->expectException(InvalidCountrySpecificationException::class);
        $this->expectExceptionMessage('The country specification must not be empty');
        Country::from2CharIso3166($emptyStringProvider);
    }

    /**
     * @return array[]
     */
    public function emptyStringDataProvider() : array
    {
        return [
            [''],
            [' '],
        ];
    }

    /**
     * @dataProvider countrySpecWithInvalidCharactersProvider
     */
    public function testItThrowsAnExceptionIfItContainsCharactersBeyondAToZ(string $outOfBoundsCountrySpec)
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
    public function countrySpecWithInvalidCharactersProvider() : array
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
