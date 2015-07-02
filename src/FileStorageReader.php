<?php

namespace Brera;

interface FileStorageReader
{
    /**
     * @param string $relativeFilePath
     * @return string
     */
    public function getFileContents($relativeFilePath);
}
