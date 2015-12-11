<?php

namespace LizardsAndPumpkins\Tax;

/**
 * @covers \LizardsAndPumpkins\Tax\TwentyOneRunTaxServiceLocator
 */
class TwentyOneRunTaxServiceLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TwentyOneRunTaxServiceLocator
     */
    private $taxServiceLocator;

    protected function setUp()
    {
        $this->taxServiceLocator = new TwentyOneRunTaxServiceLocator();
    }

    public function testItImplementsTheTaxServiceLocatorInterface()
    {
        $this->assertInstanceOf(TaxServiceLocator::class, $this->taxServiceLocator);
    }
}
