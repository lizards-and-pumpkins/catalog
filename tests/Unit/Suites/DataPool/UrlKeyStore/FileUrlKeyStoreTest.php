<?php


namespace LizardsAndPumpkins\DataPool\UrlKeyStore;

use LizardsAndPumpkins\Utils\LocalFilesystem;

/**
 * @covers \LizardsAndPumpkins\DataPool\UrlKeyStore\FileUrlKeyStore
 * @uses   \LizardsAndPumpkins\DataPool\UrlKeyStore\IntegrationTestUrlKeyStoreAbstract
 * @uses   \LizardsAndPumpkins\Utils\LocalFilesystem
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

        $urlKeyStoreOne->addUrlKeyForVersion('aaa', '1');
        $this->assertSame(['aaa'], $urlKeyStoreTwo->getForDataVersion('1'));
    }
}
