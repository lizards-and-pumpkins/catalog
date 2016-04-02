<?php

namespace LizardsAndPumpkins\Util\FileSystem;

use LizardsAndPumpkins\Util\FileSystem\Exception\DirectoryDoesNotExistException;
use LizardsAndPumpkins\Util\FileSystem\Exception\DirectoryNotWritableException;

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

    /**
     * @param string $basePath
     * @param string $path
     * @return string
     */
    public function getRelativePath($basePath, $path)
    {
        if (0 === strpos($path, $basePath) || $basePath === $path . '/') {
            return ltrim(substr($path, strlen($basePath)), '/');
        }

        if ($this->isRelativePath($path)) {
            return $path;
        }

        return $this->buildRelativePath($basePath, $path);
    }

    /**
     * @param string $basePath
     * @param string $path
     * @return string
     */
    private function buildRelativePath($basePath, $path)
    {
        $pathParts = explode('/', rtrim($path, '/'));
        $basePathParts = explode('/', rtrim($basePath, '/'));
        $commonDirCount = $this->getCountOfSharedDirectories($basePathParts, $pathParts);
        $downPath = $this->buildDownPortionOfRelativePath($commonDirCount, $basePathParts);
        $upPath = $this->buildUpPortionOfRelativePath($commonDirCount, $pathParts);

        return $downPath . $upPath . (substr($path, - 1) === '/' ? '/' : '');
    }

    /**
     * @param string[] $basePathParts
     * @param string[] $pathParts
     * @return int
     */
    private function getCountOfSharedDirectories(array $basePathParts, array $pathParts)
    {
        $commonPartCount = 0;
        for ($max = min(count($pathParts), count($basePathParts)); $commonPartCount < $max; $commonPartCount ++) {
            if ($pathParts[$commonPartCount] !== $basePathParts[$commonPartCount]) {
                break;
            }
        }

        return $commonPartCount;
    }

    /**
     * @param int $commonDirCount
     * @param string[] $basePathParts
     * @return string
     */
    private function buildDownPortionOfRelativePath($commonDirCount, array $basePathParts)
    {
        $numDown = count(array_slice($basePathParts, $commonDirCount));
        return implode('/', array_fill(0, $numDown, '..'));
    }

    /**
     * @param int $commonDirCount
     * @param string[] $pathParts
     * @return string
     */
    private function buildUpPortionOfRelativePath($commonDirCount, array $pathParts)
    {
        if ($commonDirCount === count($pathParts)) {
            return '';
        }

        return '/' . implode('/', array_slice($pathParts, $commonDirCount));
    }

    /**
     * @param string $path
     * @return bool
     */
    private function isRelativePath($path)
    {
        return substr($path, 0, 1) !== '/';
    }
}
