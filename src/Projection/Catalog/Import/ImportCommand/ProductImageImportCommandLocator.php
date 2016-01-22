<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand;

use LizardsAndPumpkins\Command;
use LizardsAndPumpkins\DataVersion;
use LizardsAndPumpkins\MasterFactory;

class ProductImageImportCommandLocator
{
    /**
     * @var ProductImageImportCommandFactory
     */
    private $factory;

    public function __construct(MasterFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param string $imageFilePath
     * @param DataVersion $dataVersion
     * @return Command[]
     */
    public function getProductImageImportCommands($imageFilePath, DataVersion $dataVersion)
    {
        return $this->factory->createProductImageImportCommands($imageFilePath, $dataVersion);
    }
}
