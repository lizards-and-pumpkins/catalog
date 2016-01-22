<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Command;
use LizardsAndPumpkins\DataVersion;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductListingCriteria;
use LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand\ProductImageImportCommandLocator;
use LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand\ProductImportCommandLocator;
use LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand\ProductListingImportCommandLocator;
use LizardsAndPumpkins\Queue\Queue;

class QueueImportCommands
{
    /**
     * @var Queue
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
        Queue $commandQueue,
        ProductImportCommandLocator $productImportCommandLocator,
        ProductImageImportCommandLocator $productImageImportCommandLocator,
        ProductListingImportCommandLocator $productListingImportCommandLocator
    ) {
        $this->commandQueue = $commandQueue;
        $this->productImportCommandLocator = $productImportCommandLocator;
        $this->imageImportCommandLocator = $productImageImportCommandLocator;
        $this->listingImportCommandLocator = $productListingImportCommandLocator;
    }

    public function forProduct(Product $product)
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

    public function forListing(ProductListingCriteria $listingCriteria)
    {
        $commands = $this->listingImportCommandLocator->getProductListingImportCommands($listingCriteria);
        $this->addCommandsToQueue($commands);
    }

    /**
     * @param Command[] $commands
     */
    private function addCommandsToQueue(array $commands)
    {
        @array_map(function (Command $command) {
            $this->commandQueue->add($command);
        }, $commands);
    }
}
