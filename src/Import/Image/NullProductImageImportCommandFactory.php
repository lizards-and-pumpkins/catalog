<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Util\Factory\FactoryTrait;

class NullProductImageImportCommandFactory implements ProductImageImportCommandFactory
{
    use FactoryTrait;

    /**
     * @param string $imageFilePath
     * @param DataVersion $dataVersion
     * @return Command[]
     */
    public function createProductImageImportCommands(string $imageFilePath, DataVersion $dataVersion) : array
    {
        return [];
    }
}
