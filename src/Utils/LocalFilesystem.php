<?php

namespace Brera\Utils;

class LocalFilesystem
{
    /**
     * @param string $directoryPath
     */
    public function removeDirectoryAndItsContent($directoryPath)
    {
        if (!is_dir($directoryPath)) {
            throw new DirectoryDoesNotExistException();
        }

        if (!is_writable($directoryPath)) {
            throw new DirectoryNotWritableException();
        }

        $directoryIterator = new \RecursiveDirectoryIterator($directoryPath, \FilesystemIterator::SKIP_DOTS);

        foreach (new \RecursiveIteratorIterator($directoryIterator, \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
            $path->isDir() && !$path->isLink() ? rmdir($path->getPathname()) : unlink($path->getPathname());
        }

        rmdir($directoryPath);
    }
}
