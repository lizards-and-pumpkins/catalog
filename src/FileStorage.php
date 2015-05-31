<?php

namespace Brera;

interface FileStorage
{
    /**
     * @param string $fileName
     * @return string
     */
    public function getFileContents($fileName);

    /**
     * @param string $fileName
     * @param string $contents
     */
    public function putFileContents($fileName, $contents);
}
