<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\Country;

use LizardsAndPumpkins\Context\ContextPartBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Context\Country\IntegrationTestContextCountry
 */
class IntegrationTestContextCountryTest extends TestCase
{
    /**
     * @var IntegrationTestContextCountry
     */
    private $contextCountry;

    protected function setUp()
    {
        $this->contextCountry = new IntegrationTestContextCountry();
    }

    public function testItIsAContextPartBuilder()
    {
        $this->assertInstanceOf(ContextPartBuilder::class, $this->contextCountry);
    }

    public function testItReturnsTheCountryContextPartCode()
    {
        $this->assertSame(Country::CONTEXT_CODE, $this->contextCountry->getCode());
    }

    public function testItReturnsTheValueFromTheInputDataSetIfPresent()
    {
        $inputDataSet = [Country::CONTEXT_CODE => 'fr'];
        $this->assertSame('fr', $this->contextCountry->getValue($inputDataSet));
    }

    public function testItReturnsDefaultCountryCodeIfNotPartOfTheInputDataSet()
    {
        $inputDataSet = [];
        $this->assertSame('DE', $this->contextCountry->getValue($inputDataSet));
    }
}
