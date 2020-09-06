<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Core\Factory\Factory;

interface ProductImageImportCommandFactory extends Factory
{
    /**
     * @param string $imageFilePath
     * @param DataVersion $dataVersion
     * @return Command[]
     */
    public function createProductImageImportCommands(string $imageFilePath, DataVersion $dataVersion) : array;
}
