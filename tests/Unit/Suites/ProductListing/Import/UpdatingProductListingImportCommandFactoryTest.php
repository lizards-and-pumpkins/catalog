<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\ProductListing\AddProductListingCommand;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\UpdatingProductListingImportCommandFactory
 * @uses   \LizardsAndPumpkins\ProductListing\AddProductListingCommand
 */
class UpdatingProductListingImportCommandFactoryTest extends TestCase
{
    /**
     * @var UpdatingProductListingImportCommandFactory
     */
    private $factory;

    final protected function setUp(): void
    {
        $this->factory = new UpdatingProductListingImportCommandFactory();
    }

    public function testItImplementsTheProductListingImportCommandFactoryInterface(): void
    {
        $this->assertInstanceOf(ProductListingImportCommandFactory::class, $this->factory);
    }

    public function testItReturnsAnAddProductListingCommand(): void
    {
        /** @var ProductListing $stubProductListing */
        $stubProductListing = $this->createMock(ProductListing::class);
        $commands = $this->factory->createProductListingImportCommands($stubProductListing);

        $this->assertIsArray($commands);
        $this->assertNotEmpty($commands);
        $this->assertContainsOnlyInstancesOf(AddProductListingCommand::class, $commands);
    }
}
