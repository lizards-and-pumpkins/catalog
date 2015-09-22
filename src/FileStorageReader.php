<?php

namespace LizardsAndPumpkins;

interface FileStorageReader
{
    /**
     * @param string $filePath
     * @return string
     */
    public function getFileContents($filePath);
}
