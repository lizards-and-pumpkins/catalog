<?php

namespace Brera\DataPool\SearchEngine;

use Brera\Product\ProductId;
use Brera\Utils\Clearable;
use Brera\Utils\LocalFilesystem;

/**
 * @covers \Brera\DataPool\SearchEngine\FileSearchEngine
 * @covers \Brera\DataPool\SearchEngine\IntegrationTestSearchEngineAbstract
 * @uses   \Brera\Context\ContextBuilder
 * @uses   \Brera\Context\ContextDecorator
 * @uses   \Brera\Context\LocaleContextDecorator
 * @uses   \Brera\Context\VersionedContext
 * @uses   \Brera\Context\WebsiteContextDecorator
 * @uses   \Brera\DataVersion
 * @uses   \Brera\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 * @uses   \Brera\DataPool\SearchEngine\SearchDocument\SearchDocument
 * @uses   \Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection
 * @uses   \Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentField
 * @uses   \Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection
 * @uses   \Brera\Product\ProductId
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
        $this->setExpectedException(SearchEngineNotAvailableException::class);
        FileSearchEngine::create('non-existing-path');
    }

    /**
     * @return SearchEngine
     */
    protected function createSearchEngineInstance()
    {
        $this->prepareTemporaryStorage();

        return FileSearchEngine::create($this->temporaryStorage);
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

    public function testItIsClearable()
    {
        $this->assertInstanceOf(Clearable::class, $this->getSearchEngine());
    }

    public function testItClearsTheStorage()
    {
        $searchDocumentFieldName = 'foo';
        $searchDocumentFieldValue = 'bar';
        $productId = ProductId::fromString('id');

        $searchDocument = $this->createSearchDocument(
            [$searchDocumentFieldName => $searchDocumentFieldValue],
            $productId
        );

        $this->getSearchEngine()->addSearchDocument($searchDocument);
        $this->getSearchEngine()->clear();
        $result = $this->getSearchEngine()->query($searchDocumentFieldValue, $this->getTestContext());

        $this->assertEmpty($result);
    }
}
