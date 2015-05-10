<?php

namespace Brera;

class LocalFilesystemStorage implements StaticFileStorage
{
    /**
     * @param string $path
     * @return string
     */
    public function getFileContents($path)
    {
        return file_get_contents($path);
    }

    /**
     * @param string $path
     * @param string $contents
     */
    public function putFileContents($path, $contents)
    {
        file_put_contents($path, $contents);
    }
}
