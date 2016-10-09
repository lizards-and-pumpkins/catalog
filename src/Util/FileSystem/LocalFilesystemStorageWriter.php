<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Util\FileSystem;

use LizardsAndPumpkins\Import\FileStorage\FileStorageWriter;
use LizardsAndPumpkins\Util\FileSystem\Exception\FileNotWritableException;

class LocalFilesystemStorageWriter implements FileStorageWriter
{
    public function putFileContents(string $filePath, string $contents)
    {
        $this->checkIfDestinationIsWritable($filePath);

        file_put_contents($filePath, $contents);
    }

    private function checkIfDestinationIsWritable(string $filePath)
    {
        if (file_exists($filePath) && !is_writable($filePath) ||
            !file_exists($filePath) && !is_writable(dirname($filePath))
        ) {
            throw new FileNotWritableException(sprintf('Can not write %s file', $filePath));
        }
    }
}
