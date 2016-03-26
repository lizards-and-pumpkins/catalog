<?php

namespace LizardsAndPumpkins\Util\FileSystem;

use LizardsAndPumpkins\Import\FileStorage\FileStorageReader;
use LizardsAndPumpkins\Util\FileSystem\Exception\FileNotReadableException;

class LocalFilesystemStorageReader implements FileStorageReader
{

    /**
     * @param string $filePath
     * @return string
     */
    public function getFileContents($filePath)
    {
        $this->checkIfFileIsReadable($filePath);

        return file_get_contents($filePath);
    }

    /**
     * @param string $filePath
     */
    private function checkIfFileIsReadable($filePath)
    {
        if (!is_file($filePath)) {
            throw new FileNotReadableException(sprintf('Can not read %s file', $filePath));
        }
    }
}
