<?php

namespace LizardsAndPumpkins\Import\FileStorage;

interface FileStorageWriter
{
    /**
     * @param string $filePath
     * @param string $contents
     */
    public function putFileContents($filePath, $contents);
}
