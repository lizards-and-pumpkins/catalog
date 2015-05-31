<?php

namespace Brera;

interface FileStorageReader
{
    /**
     * @param string $fileName
     * @return string
     */
    public function getFileContents($fileName);
}
