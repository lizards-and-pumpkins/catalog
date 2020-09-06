<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\UrlKeyStore;

use LizardsAndPumpkins\Util\FileSystem\Exception\DirectoryDoesNotExistException;
use LizardsAndPumpkins\Util\FileSystem\LocalFilesystem;

/**
 * @covers \LizardsAndPumpkins\DataPool\UrlKeyStore\FileUrlKeyStore
 * @uses   \LizardsAndPumpkins\DataPool\UrlKeyStore\IntegrationTestUrlKeyStoreAbstract
 * @uses   \LizardsAndPumpkins\Util\FileSystem\LocalFilesystem
 */
class FileUrlKeyStoreTest extends AbstractIntegrationTestUrlKeyStoreTest
{
    /**
     * @var bool
     */
    public static $createDirectory = true;

    /**
     * @var string
     */
    private $temporaryStoragePath;

    private static function isDirectoryExpected(): bool
    {
        return self::$createDirectory;
    }

    final protected function createUrlKeyStoreInstance(): FileUrlKeyStore
    {
        $this->temporaryStoragePath = $this->prepareTemporaryStorage();

        return new FileUrlKeyStore($this->temporaryStoragePath);
    }

    private function prepareTemporaryStorage(): string
    {
        $temporaryStoragePath = sys_get_temp_dir() . '/lizards-and-pumpkins-test-url-key-storage';

        if (file_exists($temporaryStoragePath)) {
            (new LocalFilesystem())->removeDirectoryAndItsContent($temporaryStoragePath);
        }

        mkdir($temporaryStoragePath);
        return $temporaryStoragePath;
    }

    final protected function setUp(): void
    {
        self::$createDirectory = true;
        parent::setUp();
    }

    final protected function tearDown(): void
    {
        try {
            (new LocalFilesystem())->removeDirectoryAndItsContent($this->temporaryStoragePath);
        } catch (DirectoryDoesNotExistException $e) {
            if (self::isDirectoryExpected()) {
                throw $e;
            }
        }

    }

    public function testAddOnOneInstanceReadFromOther(): void
    {
        $urlKeyStoreOne = $this->createUrlKeyStoreInstance();
        $urlKeyStoreTwo = $this->createUrlKeyStoreInstance();

        $urlKeyStoreOne->addUrlKeyForVersion('1.0', 'example.html', 'dummy-context-string', 'type-string');
        $this->assertSame(
            [['example.html', 'dummy-context-string', 'type-string']],
            $urlKeyStoreTwo->getForDataVersion('1.0')
        );
    }

    public function testItCanStoreContextDataWithASpace(): void
    {
        $urlKeyStore = $this->createUrlKeyStoreInstance();
        $urlKeyStore->addUrlKeyForVersion('1.0', 'example.html', 'context data with spaces', 'type-string');
        $this->assertSame(
            [['example.html', 'context data with spaces', 'type-string']],
            $urlKeyStore->getForDataVersion('1.0')
        );
    }

    public function testItCreatesTheStorageDirectoryIfItDoesNotExist(): void
    {
        $urlKeyStore = $this->createUrlKeyStoreInstance();
        rmdir($this->temporaryStoragePath);
        $urlKeyStore->addUrlKeyForVersion('1.0', 'example.html', 'context-data', 'type-string');
        $this->assertFileExists($this->temporaryStoragePath);
        $this->assertTrue(is_dir($this->temporaryStoragePath));
    }

    public function testItThrowsAnExceptionIfDirectoryIsNotCreated(): void
    {
        $urlKeyStore = $this->createUrlKeyStoreInstance();
        rmdir($this->temporaryStoragePath);

        $this->expectException(DirectoryDoesNotExistException::class);
        $this->expectExceptionMessage(
            sprintf('Directory "%s" was not found and could not be created to store urls in %s',
                $this->temporaryStoragePath,
                get_class($urlKeyStore)
            )
        );

        self::$createDirectory = false;
        $urlKeyStore->addUrlKeyForVersion('1.0', 'example.html', 'context-data', 'type-string');
    }
}

function mkdir($pathname, $mode = 0777, $recursive = false, $context = null): bool
{
    if (! FileUrlKeyStoreTest::$createDirectory) {
        return false;
    }
    if ($context !== null) {
        return \mkdir($pathname, $mode, $recursive, $context);
    }
    return \mkdir($pathname, $mode, $recursive);
}
