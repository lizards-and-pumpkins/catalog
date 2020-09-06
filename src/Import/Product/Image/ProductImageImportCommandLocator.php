<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\Image;

use LizardsAndPumpkins\Import\Image\ProductImageImportCommandFactory;
use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Core\Factory\MasterFactory;

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
    public function getProductImageImportCommands(string $imageFilePath, DataVersion $dataVersion) : array
    {
        return $this->factory->createProductImageImportCommands($imageFilePath, $dataVersion);
    }
}
