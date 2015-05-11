<?php

namespace Brera\Utils;

class LocalFilesystem
{
    public function removeDirectoryAndItsContent($directoryPath)
    {
        $directoryIterator = new \RecursiveDirectoryIterator($directoryPath, \FilesystemIterator::SKIP_DOTS);

        foreach(new \RecursiveIteratorIterator($directoryIterator, \RecursiveIteratorIterator::CHILD_FIRST) as $path) {
            $path->isDir() && !$path->isLink() ? rmdir($path->getPathname()) : unlink($path->getPathname());
        }

        rmdir($directoryPath);
    }
}
