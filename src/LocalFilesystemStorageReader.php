<?php

namespace Brera;

use Brera\Utils\FileNotReadableException;

class LocalFilesystemStorageReader implements FileStorageReader
{
    /**
     * @var string
     */
    private $baseDirPath;

    /**
     * @param string $baseDirPath
     */
    public function __construct($baseDirPath)
    {
        $this->baseDirPath = $baseDirPath;
    }

    /**
     * @param string $relativeFilePath
     * @return string
     */
    public function getFileContents($relativeFilePath)
    {
        $filePath = $this->getAbsoluteFilePath($relativeFilePath);
        $this->checkFileIsReadable($filePath);

        return file_get_contents($filePath);
    }

    /**
     * @param string $relativeFilePath
     * @return string
     */
    private function getAbsoluteFilePath($relativeFilePath)
    {
        $filePath = $this->baseDirPath . '/' . $relativeFilePath;
        return $filePath;
    }

    /**
     * @param string $filePath
     * @throws FileNotReadableException
     */
    private function checkFileIsReadable($filePath)
    {
        if (!is_file($filePath)) {
            throw new FileNotReadableException(sprintf('Can not read %s file', $filePath));
        }
    }
}
