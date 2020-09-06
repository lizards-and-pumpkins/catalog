<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Util\FileSystem;

use LizardsAndPumpkins\Import\FileStorage\FileStorageReader;
use LizardsAndPumpkins\Util\FileSystem\Exception\FileNotReadableException;

class LocalFilesystemStorageReader implements FileStorageReader
{

    public function getFileContents(string $filePath) : string
    {
        $this->checkIfFileIsReadable($filePath);

        return file_get_contents($filePath);
    }

    private function checkIfFileIsReadable(string $filePath): void
    {
        if (!is_file($filePath)) {
            throw new FileNotReadableException(sprintf('Can not read %s file', $filePath));
        }
    }
}
