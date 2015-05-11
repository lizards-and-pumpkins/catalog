<?php

namespace Brera;

class LocalImage implements StaticFile
{
    /**
     * @var string
     */
    private $originalImageDir;

    /**
     * @var string
     */
    private $resultImageDir;

    /**
     * @param string $originalImageDir
     * @param string $resultImageDir
     */
    public function __construct($originalImageDir, $resultImageDir)
    {
        $this->originalImageDir = $originalImageDir;
        $this->resultImageDir = $resultImageDir;
    }

    /**
     * @param string $fileName
     * @return string
     */
    public function getFileContents($fileName)
    {
        return file_get_contents($this->originalImageDir . '/' . $fileName);
    }

    /**
     * @param string $fileName
     * @param string $contents
     */
    public function putFileContents($fileName, $contents)
    {
        file_put_contents($this->resultImageDir . '/' . $fileName, $contents);
    }
}
