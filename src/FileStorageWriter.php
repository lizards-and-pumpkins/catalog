<?php

namespace LizardsAndPumpkins;

interface FileStorageWriter
{
    /**
     * @param string $relativeFilePath
     * @param string $contents
     */
    public function putFileContents($relativeFilePath, $contents);
}
