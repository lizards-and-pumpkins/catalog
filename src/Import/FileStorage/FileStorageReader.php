<?php

namespace LizardsAndPumpkins\Import\FileStorage;

interface FileStorageReader
{
    /**
     * @param string $filePath
     * @return string
     */
    public function getFileContents($filePath);
}
