<?php

namespace LizardsAndPumpkins;

trait TestFileFixtureTrait
{
    /**
     * @var string[]
     */
    private $fixtureDirs = [];

    /**
     * @var string[]
     */
    private $fixtureFiles = [];

    /**
     * @var string
     */
    private $uniqueId;

    /**
     * @param string $filePath
     * @param string $content
     * @param int $mode
     */
    public function createFixtureFile($filePath, $content, $mode = 0600)
    {
        $realFile = $this->___getAbsolutePath($filePath);
        $this->___createMissingDirectories($realFile);
        $this->___createFile($content, $realFile, $mode);
        $this->fixtureFiles[] = $realFile;
    }

    /**
     * @param string $realFile
     */
    public function addFileToCleanupAfterTest($realFile)
    {
        $this->fixtureFiles[] = $realFile;
    }

    /**
     * @param string $directoryPath
     */
    public function createFixtureDirectory($directoryPath)
    {
        $absolutePath = $this->___getAbsolutePath($directoryPath);
        $directories = explode('/', ltrim($absolutePath, '/'));
        $this->___createMissingDirectoriesRecursively($directories);
    }

    /**
     * @return string
     */
    public function getUniqueTempDir()
    {
        return sys_get_temp_dir() . '/lizards-and-pumpkins/test/' . $this->___getUniqueId();
    }

    /**
     * @after
     */
    protected function ___cleanupFilesystemFixtures()
    {
        $this->___cleanUpFixtureFiles();
        $this->___cleanUpFixtureDirsRecursively(array_reverse($this->fixtureDirs));
    }

    /**
     * @param string $path
     * @return string
     */
    private function ___getAbsolutePath($path)
    {
        if ('/' === substr($path, 0, 1)) {
            return $path;
        }

        return getcwd() . '/' . $path;
    }

    /**
     * @param string $realFile
     */
    private function ___createMissingDirectories($realFile)
    {
        $dirs = explode('/', ltrim(dirname($realFile), '/'));
        $this->___createMissingDirectoriesRecursively($dirs);
    }

    /**
     * @param string[] $dirs
     * @param string $base
     */
    private function ___createMissingDirectoriesRecursively(array $dirs, $base = '')
    {
        if (0 == count($dirs)) {
            return;
        }
        $dir = '' !== $dirs[0] ?
            $base . '/' . $dirs[0] :
            $base;
        $this->___createDirectoryIfNotExists($dir);
        $this->___validateIsDir($dir);
        $this->___createMissingDirectoriesRecursively(array_slice($dirs, 1), $dir);
    }

    /**
     * @param string $dir
     */
    private function ___createDirectoryIfNotExists($dir)
    {
        if (!file_exists($dir)) {
            mkdir($dir);
            $this->fixtureDirs[] = $dir;
        }
    }

    /**
     * @param string $dir
     */
    private function ___validateIsDir($dir)
    {
        if (!file_exists($dir)) {
            throw new \RuntimeException(sprintf('Unable to create directory "%s"', $dir));
        }
        if (!is_dir($dir)) {
            throw new \RuntimeException(sprintf('The file system path exists but is not a directory: "%s"', $dir));
        }
    }

    /**
     * @param string $file
     */
    private function ___validateFileWasCreated($file)
    {
        if (!file_exists($file)) {
            throw new \RuntimeException('Unable to create fixture file "%s"', $file);
        }
    }

    /**
     * @param string $content
     * @param string $file
     * @param int $mode
     */
    private function ___createFile($content, $file, $mode = 0500)
    {
        $this->___validateFileDoesNotExist($file);
        file_put_contents($file, $content);
        chmod($file, $mode);
        $this->___validateFileWasCreated($file);
    }

    /**
     * @param string $file
     */
    private function ___validateFileDoesNotExist($file)
    {
        if (file_exists($file)) {
            throw new \RuntimeException(sprintf('Fixture file already exists: "%s"', $file));
        }
    }

    private function ___cleanUpFixtureFiles()
    {
        array_map(function ($file) {
            if (file_exists($file)) {
                if (!is_writable($file)) {
                    chmod($file, 0500);
                }
                unlink($file);
            }
        }, $this->fixtureFiles);
    }

    /**
     * @param string[] $dirs
     */
    private function ___cleanUpFixtureDirsRecursively(array $dirs)
    {
        if (0 == count($dirs)) {
            return;
        }
        if (is_dir($dirs[0])) {
            rmdir($dirs[0]);
        }
        $this->___cleanUpFixtureDirsRecursively(array_slice($dirs, 1));
    }

    /**
     * @return string
     */
    private function ___getUniqueId()
    {
        if (is_null($this->uniqueId)) {
            $this->uniqueId = uniqid();
        }
        return $this->uniqueId;
    }
}
