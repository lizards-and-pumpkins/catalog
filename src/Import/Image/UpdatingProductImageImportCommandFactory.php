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
     * @return array[]
     */
    public function createProductImageImportCommands($imageFilePath, DataVersion $dataVersion)
    {
        $payload = ['file_path' => $imageFilePath, 'data_version' => (string)$dataVersion];
        return [['name' => 'add_image', 'payload' => json_encode($payload)]];
    }
}
