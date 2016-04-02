<?php

namespace LizardsAndPumpkins\DataPool\UrlKeyStore;

use LizardsAndPumpkins\Util\FileSystem\LocalFilesystem;

/**
 * @covers \LizardsAndPumpkins\DataPool\UrlKeyStore\FileUrlKeyStore
 * @uses   \LizardsAndPumpkins\DataPool\UrlKeyStore\IntegrationTestUrlKeyStoreAbstract
 * @uses   \LizardsAndPumpkins\Util\FileSystem\LocalFilesystem
 */
class FileUrlKeyStoreTest extends AbstractIntegrationTestUrlKeyStoreTest
{
    /**
     * @var string
     */
    private $temporaryStoragePath;

    /**
     * @return FileUrlKeyStore
     */
    protected function createUrlKeyStoreInstance()
    {
        $this->temporaryStoragePath = $this->prepareTemporaryStorage();

        return new FileUrlKeyStore($this->temporaryStoragePath);
    }

    /**
     * @return string
     */
    private function prepareTemporaryStorage()
    {
        $temporaryStoragePath = sys_get_temp_dir() . '/lizards-and-pumpkins-test-url-key-storage';

        if (file_exists($temporaryStoragePath)) {
            (new LocalFilesystem())->removeDirectoryAndItsContent($temporaryStoragePath);
        }

        mkdir($temporaryStoragePath);
        return $temporaryStoragePath;
    }

    protected function tearDown()
    {
        (new LocalFilesystem())->removeDirectoryAndItsContent($this->temporaryStoragePath);
    }

    public function testAddOnOneInstanceReadFromOther()
    {
        $urlKeyStoreOne = $this->createUrlKeyStoreInstance();
        $urlKeyStoreTwo = $this->createUrlKeyStoreInstance();

        $urlKeyStoreOne->addUrlKeyForVersion('1.0', 'example.html', 'dummy-context-string', 'type-string');
        $this->assertSame(
            [['example.html', 'dummy-context-string', 'type-string']],
            $urlKeyStoreTwo->getForDataVersion('1.0')
        );
    }

    public function testItCanStoreContextDataWithASpace()
    {
        $urlKeyStore = $this->createUrlKeyStoreInstance();
        $urlKeyStore->addUrlKeyForVersion('1.0', 'example.html', 'context data with spaces', 'type-string');
        $this->assertSame(
            [['example.html', 'context data with spaces', 'type-string']],
            $urlKeyStore->getForDataVersion('1.0')
        );
    }

    public function testItCreatesTheStorageDirectoryIfItDoesNotExist()
    {
        $urlKeyStore = $this->createUrlKeyStoreInstance();
        rmdir($this->temporaryStoragePath);
        $urlKeyStore->addUrlKeyForVersion('1.0', 'example.html', 'context-data', 'type-string');
        $this->assertFileExists($this->temporaryStoragePath);
        $this->assertTrue(is_dir($this->temporaryStoragePath));
    }
}
