<?php

namespace Brera;

interface StaticFile
{
    /**
     * @param string $path
     * @return string
     */
    public function getFileContents($path);

    /**
     * @param string $path
     * @param string $contents
     */
    public function putFileContents($path, $contents);
}
