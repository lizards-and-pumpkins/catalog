<?php

namespace LizardsAndPumpkins\Utils;

use LizardsAndPumpkins\Utils\Exception\DirectoryDoesNotExistException;
use LizardsAndPumpkins\Utils\Exception\DirectoryNotWritableException;

class LocalFilesystem
{
    /**
     * @param string $directoryPath
     */
    public function removeDirectoryAndItsContent($directoryPath)
    {
        if (!is_dir($directoryPath)) {
            throw new DirectoryDoesNotExistException(sprintf('The directory "%s" does not exist', $directoryPath));
        }

        if (!is_writable($directoryPath)) {
            throw new DirectoryNotWritableException(sprintf('The directory "%s" is not writable', $directoryPath));
        }

        $directoryIterator = new \RecursiveDirectoryIterator($directoryPath, \FilesystemIterator::SKIP_DOTS);

        foreach (new \RecursiveIteratorIterator($directoryIterator, \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
            $path->isDir() && !$path->isLink() ? rmdir($path->getPathname()) : unlink($path->getPathname());
        }

        rmdir($directoryPath);
    }

    /**
     * @param string $directoryPath
     */
    public function removeDirectoryContents($directoryPath)
    {
        if (!file_exists($directoryPath)) {
            return;
        }
        if (!is_dir($directoryPath)) {
            $message = sprintf('The given path is not a directory: "%s"', $directoryPath);
            throw new Exception\NotADirectoryException($message);
        }
        $directoryIterator = new \RecursiveDirectoryIterator($directoryPath, \FilesystemIterator::SKIP_DOTS);
        foreach ($directoryIterator as $path) {
            is_dir($path->getPathname()) ?
                $this->removeDirectoryAndItsContent($path->getPathname()) :
                unlink($path->getPathname());
        }
    }
}
