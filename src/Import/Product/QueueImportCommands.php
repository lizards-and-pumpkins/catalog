<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;
use LizardsAndPumpkins\Import\Product\Image\ProductImageImportCommandLocator;
use LizardsAndPumpkins\Import\Product\Listing\ProductListingImportCommandLocator;

class QueueImportCommands
{
    /**
     * @var CommandQueue
     */
    private $commandQueue;

    /**
     * @var ProductImportCommandLocator
     */
    private $productImportCommandLocator;

    /**
     * @var ProductImageImportCommandLocator
     */
    private $imageImportCommandLocator;

    /**
     * @var ProductListingImportCommandLocator
     */
    private $listingImportCommandLocator;

    public function __construct(
        CommandQueue $commandQueue,
        ProductImportCommandLocator $productImportCommandLocator,
        ProductImageImportCommandLocator $productImageImportCommandLocator,
        ProductListingImportCommandLocator $productListingImportCommandLocator
    ) {
        $this->commandQueue = $commandQueue;
        $this->productImportCommandLocator = $productImportCommandLocator;
        $this->imageImportCommandLocator = $productImageImportCommandLocator;
        $this->listingImportCommandLocator = $productListingImportCommandLocator;
    }

    public function forProduct(Product $product): void
    {
        $commands = $this->productImportCommandLocator->getProductImportCommands($product);
        $this->addCommandsToQueue(...$commands);
    }

    public function forImage(string $imageFilePath, DataVersion $dataVersion): void
    {
        $commands = $this->imageImportCommandLocator->getProductImageImportCommands($imageFilePath, $dataVersion);
        $this->addCommandsToQueue(...$commands);
    }

    public function forListing(ProductListing $listingCriteria): void
    {
        $commands = $this->listingImportCommandLocator->getProductListingImportCommands($listingCriteria);
        $this->addCommandsToQueue(...$commands);
    }

    private function addCommandsToQueue(Command ...$commands): void
    {
        every($commands, [$this->commandQueue, 'add']);
    }
}
