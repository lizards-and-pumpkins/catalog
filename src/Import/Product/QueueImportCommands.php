<?php

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

    public function forProduct(ProductDTO $product)
    {
        $commands = $this->productImportCommandLocator->getProductImportCommands($product);
        $this->addCommandsToQueue($commands);
    }

    /**
     * @param string $imageFilePath
     * @param DataVersion $dataVersion
     */
    public function forImage($imageFilePath, DataVersion $dataVersion)
    {
        $commands = $this->imageImportCommandLocator->getProductImageImportCommands($imageFilePath, $dataVersion);
        $this->addCommandsToQueue($commands);
    }

    public function forListing(ProductListing $listingCriteria)
    {
        $commands = $this->listingImportCommandLocator->getProductListingImportCommands($listingCriteria);
        $this->addCommandsToQueue($commands);
    }

    /**
     * @param Command[] $commands
     */
    private function addCommandsToQueue(array $commands)
    {
        every($commands, [$this->commandQueue, 'add']);
    }
}
