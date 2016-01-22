<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand;

use LizardsAndPumpkins\Command;
use LizardsAndPumpkins\DataVersion;
use LizardsAndPumpkins\FactoryTrait;
use LizardsAndPumpkins\Image\AddImageCommand;

class UpdatingProductImageImportCommandFactory implements ProductImageImportCommandFactory
{
    use FactoryTrait;

    /**
     * @param string $imageFilePath
     * @param DataVersion $dataVersion
     * @return Command[]
     */
    public function createProductImageImportCommands($imageFilePath, DataVersion $dataVersion)
    {
        return [new AddImageCommand($imageFilePath, $dataVersion)];
    }
}
