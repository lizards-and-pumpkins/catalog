<?php

declare(strict_types=1);

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

    public function createFixtureFile(string $filePath, string $content, int $mode = 0600): void
    {
        $realFile = $this->___getAbsolutePath($filePath);
        $this->___createMissingDirectories($realFile);
        $this->___createFile($content, $realFile, $mode);
        $this->addFileToCleanupAfterTest($realFile);
    }

    public function addFileToCleanupAfterTest(string $realFile): void
    {
        $this->fixtureFiles[] = $realFile;
    }

    public function createFixtureDirectory(string $directoryPath): void
    {
        $absolutePath = $this->___getAbsolutePath($directoryPath);
        $directories = explode('/', ltrim($absolutePath, '/'));
        $this->___createMissingDirectoriesRecursively($directories);
    }

    public function getUniqueTempDir() : string
    {
        return sys_get_temp_dir() . '/lizards-and-pumpkins/test/' . $this->___getUniqueId();
    }

    /**
     * @after
     */
    protected function ___cleanupFilesystemFixtures(): void
    {
        $this->___cleanUpFixtureFiles();
        $this->___cleanUpFixtureDirsRecursively(...array_reverse($this->fixtureDirs));
    }

    private function ___getAbsolutePath(string $path) : string
    {
        if ('/' === substr($path, 0, 1)) {
            return $path;
        }

        return getcwd() . '/' . $path;
    }

    private function ___createMissingDirectories(string $realFile): void
    {
        $dirs = explode('/', ltrim(dirname($realFile), '/'));
        $this->___createMissingDirectoriesRecursively($dirs);
    }

    /**
     * @param string[] $dirs
     * @param string $base
     */
    private function ___createMissingDirectoriesRecursively(array $dirs, string $base = ''): void
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

    private function ___createDirectoryIfNotExists(string $dir): void
    {
        if (!file_exists($dir)) {
            mkdir($dir);
            $this->fixtureDirs[] = $dir;
        }
    }

    private function ___validateIsDir(string $dir): void
    {
        if (!file_exists($dir)) {
            throw new \RuntimeException(sprintf('Unable to create directory "%s"', $dir));
        }
        if (!is_dir($dir)) {
            throw new \RuntimeException(sprintf('The file system path exists but is not a directory: "%s"', $dir));
        }
    }

    private function ___validateFileWasCreated(string $file): void
    {
        if (!file_exists($file)) {
            throw new \RuntimeException('Unable to create fixture file "%s"', $file);
        }
    }

    private function ___createFile(string $content, string $file, int $mode = 0500): void
    {
        $this->___validateFileDoesNotExist($file);
        file_put_contents($file, $content);
        chmod($file, $mode);
        $this->___validateFileWasCreated($file);
    }

    private function ___validateFileDoesNotExist(string $file): void
    {
        if (file_exists($file)) {
            throw new \RuntimeException(sprintf('Fixture file already exists: "%s"', $file));
        }
    }

    private function ___cleanUpFixtureFiles(): void
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

    private function ___cleanUpFixtureDirsRecursively(string ...$dirs): void
    {
        if (0 == count($dirs)) {
            return;
        }
        if (is_dir($dirs[0])) {
            rmdir($dirs[0]);
        }
        $this->___cleanUpFixtureDirsRecursively(...array_slice($dirs, 1));
    }

    private function ___getUniqueId() : string
    {
        if (is_null($this->uniqueId)) {
            $this->uniqueId = uniqid();
        }
        return $this->uniqueId;
    }
}
