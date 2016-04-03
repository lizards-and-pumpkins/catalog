<?php

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;

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
