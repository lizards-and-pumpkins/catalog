<?php

namespace LizardsAndPumpkins\ProductListing\Import;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\UpdatingProductListingImportCommandFactory
 * @uses   \LizardsAndPumpkins\ProductListing\AddProductListingCommand
 */
class UpdatingProductListingImportCommandFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpdatingProductListingImportCommandFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = new UpdatingProductListingImportCommandFactory();
    }

    public function testItImplementsTheProductListingImportCommandFactoryInterface()
    {
        $this->assertInstanceOf(ProductListingImportCommandFactory::class, $this->factory);
    }

    public function testItReturnsAnAddProductListingCommand()
    {
        $stubProductListing = $this->getMock(ProductListing::class, [], [], '', false);
        $commands = $this->factory->createProductListingImportCommands($stubProductListing);
        $this->assertInternalType('array', $commands);
        
        $expectedPayload = [];
        array_map(function (array $commandData) use ($expectedPayload) {
            if (! isset($commandData['name']) || 'add_product_listing' !== $commandData['name']) {
                $this->fail(
                    '"name" array record must contain the command name "add_product_listing", got ' .
                    $commandData['name']
                );
            }

            if (! isset($commandData['payload'])) {
                $this->fail('"payload" array record not found');
            }
        }, $commands);
    }
}
