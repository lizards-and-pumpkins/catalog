<?php

namespace Brera;

interface StaticFile
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
