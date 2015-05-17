<?php

namespace Brera;

use Brera\Utils\FileNotReadableException;

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
     * @throws FileNotReadableException
     */
    public function getFileContents($fileName)
    {
        $filePath = $this->originalImageDir . '/' . $fileName;

        if (!is_file($filePath)) {
            throw new FileNotReadableException(sprintf('Can not read %s file', $filePath));
        }

        return file_get_contents($filePath);
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
