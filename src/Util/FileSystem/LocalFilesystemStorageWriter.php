<?php

namespace LizardsAndPumpkins\Util\FileSystem;

use LizardsAndPumpkins\Import\FileStorage\FileStorageWriter;
use LizardsAndPumpkins\Util\FileSystem\Exception\FileNotWritableException;

class LocalFilesystemStorageWriter implements FileStorageWriter
{
    /**
     * @param string $filePath
     * @param string $contents
     */
    public function putFileContents($filePath, $contents)
    {
        $this->checkIfDestinationIsWritable($filePath);

        file_put_contents($filePath, $contents);
    }

    /**
     * @param string $filePath
     */
    private function checkIfDestinationIsWritable($filePath)
    {
        if (file_exists($filePath) && !is_writable($filePath) ||
            !file_exists($filePath) && !is_writable(dirname($filePath))
        ) {
            throw new FileNotWritableException(sprintf('Can not write %s file', $filePath));
        }
    }
}
