<?php

namespace LizardsAndPumpkins\Import\Product\Image;

use LizardsAndPumpkins\Import\Image\ProductImageImportCommandFactory;
use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

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
