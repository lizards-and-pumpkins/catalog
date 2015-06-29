<?php

namespace Brera\DataPool\SearchEngine;

use Brera\Utils\LocalFilesystem;

/**
 * @covers \Brera\DataPool\SearchEngine\FileSearchEngine
 * @covers \Brera\DataPool\SearchEngine\IntegrationTestSearchEngineAbstract
 * @uses   \Brera\DataPool\SearchEngine\SearchDocument\SearchDocument
 * @uses   \Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentField
 * @uses   \Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection
 * @uses   \Brera\Utils\LocalFileSystem
 */
class FileSearchEngineTest extends AbstractSearchEngineTest
{
    /**
     * @var string
     */
    private $temporaryStorage;

    protected function tearDown()
    {
        $localFilesystem = new LocalFilesystem();
        $localFilesystem->removeDirectoryAndItsContent($this->temporaryStorage);
    }

    public function testExceptionIsThrownIfSearchEngineStorageDirIsNotWritable()
    {
        $this->setExpectedException(
            SearchEngineNotAvailableException::class,
            'Directory "" is not writable by the filesystem search engine.'
        );
        FileSearchEngine::withPath('non-existing-path');
    }

    public function testSearchEngineInterfaceIsImplemented()
    {
        $searchEngine = FileSearchEngine::withDefaultPath();
        $this->assertInstanceOf(SearchEngine::class, $searchEngine);
    }

    /**
     * @return SearchEngine
     */
    protected function createSearchEngineInstance()
    {
        $this->prepareTemporaryStorage();

        return FileSearchEngine::withPath($this->temporaryStorage);
    }

    private function prepareTemporaryStorage()
    {
        $this->temporaryStorage = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'brera-search-engine-storage';

        if (file_exists($this->temporaryStorage)) {
            $localFilesystem = new LocalFilesystem();
            $localFilesystem->removeDirectoryAndItsContent($this->temporaryStorage);
        }

        mkdir($this->temporaryStorage);
    }
}
