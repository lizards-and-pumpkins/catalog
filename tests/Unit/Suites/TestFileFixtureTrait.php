<?php


namespace Brera;

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
     * @param string $file
     * @param string $content
     * @param int $mode
     */
    public function createFixtureFile($file, $content, $mode = 0500)
    {
        $realFile = $this->getAbsolutePath($file);
        $this->createMissingDirectories($realFile);
        $this->createFile($content, $realFile, $mode);
        $this->fixtureFiles[] = $realFile;
    }

    public function createFixtureDirectory($directoryPath)
    {
        $absolutePath = $this->getAbsolutePath($directoryPath);
        $directories = explode('/', ltrim($absolutePath, '/'));
        $this->createMissingDirectoriesRecursively($directories);
    }

    /**
     * @return string
     */
    public function getUniqueTempDir()
    {
        return sys_get_temp_dir() . '/brera/test/' . $this->getUniqueId();
    }

    protected function tearDown()
    {
        parent::tearDown();
        $this->cleanUpFixtureFiles();
        $this->cleanUpFixtureDirsRecursively(array_reverse($this->fixtureDirs));
    }

    /**
     * @param string $path
     * @return string
     */
    private function getAbsolutePath($path)
    {
        if ('/' === substr($path, 0, 1)) {
            return $path;
        }

        return getcwd() . '/' . $path;
    }

    /**
     * @param string $realFile
     */
    private function createMissingDirectories($realFile)
    {
        $dirs = explode('/', ltrim(dirname($realFile), '/'));
        $this->createMissingDirectoriesRecursively($dirs);
    }

    /**
     * @param string[] $dirs
     * @param string $base
     */
    private function createMissingDirectoriesRecursively(array $dirs, $base = '')
    {
        if (0 == count($dirs)) {
            return;
        }
        $dir = '' !== $dirs[0] ?
            $base . '/' . $dirs[0] :
            $base;
        $this->createDirectoryIfNotExists($dir);
        $this->validateIsDir($dir);
        $this->createMissingDirectoriesRecursively(array_slice($dirs, 1), $dir);
    }

    /**
     * @param string $dir
     */
    private function createDirectoryIfNotExists($dir)
    {
        if (!file_exists($dir)) {
            mkdir($dir);
            $this->fixtureDirs[] = $dir;
        }
    }

    /**
     * @param string $dir
     */
    private function validateIsDir($dir)
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
    private function validateFileWasCreated($file)
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
    private function createFile($content, $file, $mode = 0500)
    {
        $this->validateFileDoesNotExist($file);
        file_put_contents($file, $content);
        chmod($file, $mode);
        $this->validateFileWasCreated($file);
    }

    /**
     * @param string $file
     */
    private function validateFileDoesNotExist($file)
    {
        if (file_exists($file)) {
            throw new \RuntimeException(sprintf('Fixture file already exists: "%s"', $file));
        }
    }

    private function cleanUpFixtureFiles()
    {
        array_map(function ($file) {
            if (! is_writable($file)) {
                chmod($file, 0500);
            }
            unlink($file);
        }, $this->fixtureFiles);
    }

    /**
     * @param string[] $dirs
     */
    private function cleanUpFixtureDirsRecursively(array $dirs)
    {
        if (0 == count($dirs)) {
            return;
        }
        rmdir($dirs[0]);
        $this->cleanUpFixtureDirsRecursively(array_slice($dirs, 1));
    }

    /**
     * @return string
     */
    private function getUniqueId()
    {
        if (is_null($this->uniqueId)) {
            $this->uniqueId = uniqid();
        }
        return $this->uniqueId;
    }
}
