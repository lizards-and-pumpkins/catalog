<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Utils\FileNotWritableException;

class LocalFilesystemStorageWriter implements FileStorageWriter
{
    /**
     * @var string
     */
    private $baseDirPath;

    /**
     * @param string $resultImageDir
     */
    public function __construct($resultImageDir)
    {
        $this->baseDirPath = $resultImageDir;
    }

    /**
     * @param string $relativeFilePath
     * @param string $contents
     */
    public function putFileContents($relativeFilePath, $contents)
    {
        $filePath = $this->getAbsoluteFilePath($relativeFilePath);
        $this->checkIfDestinationIsWritable($filePath);

        file_put_contents($filePath, $contents);
    }

    /**
     * @param string $relativeFilePath
     * @return string
     */
    private function getAbsoluteFilePath($relativeFilePath)
    {
        return $this->baseDirPath . '/' . $relativeFilePath;
    }

    /**
     * @param string $filePath
     */
    private function checkIfDestinationIsWritable($filePath)
    {
        if (file_exists($filePath) && !is_writable($filePath) ||
            !file_exists($filePath) && !is_writable(dirname($filePath))
        ) {
            throw new FileNotWritableException(sprintf('Can not write %s file', $filePath));
        }
    }
}
