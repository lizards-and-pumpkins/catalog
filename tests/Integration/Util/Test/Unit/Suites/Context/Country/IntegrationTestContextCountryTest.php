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

    final protected function setUp(): void
    {
        $this->contextCountry = new IntegrationTestContextCountry();
    }

    public function testItIsAContextPartBuilder(): void
    {
        $this->assertInstanceOf(ContextPartBuilder::class, $this->contextCountry);
    }

    public function testItReturnsTheCountryContextPartCode(): void
    {
        $this->assertSame(Country::CONTEXT_CODE, $this->contextCountry->getCode());
    }

    public function testItReturnsTheValueFromTheInputDataSetIfPresent(): void
    {
        $inputDataSet = [Country::CONTEXT_CODE => 'fr'];
        $this->assertSame('fr', $this->contextCountry->getValue($inputDataSet));
    }

    public function testItReturnsDefaultCountryCodeIfNotPartOfTheInputDataSet(): void
    {
        $inputDataSet = [];
        $this->assertSame('DE', $this->contextCountry->getValue($inputDataSet));
    }
}
