<?php

namespace LizardsAndPumpkins\Tests\Integration;

use LizardsAndPumpkins\CommonFactory;
use LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig;
use LizardsAndPumpkins\DataPool\KeyValue\File\FileKeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequest;
use LizardsAndPumpkins\DataPool\SearchEngine\FileSearchEngine;
use LizardsAndPumpkins\DataPool\UrlKeyStore\FileUrlKeyStore;
use LizardsAndPumpkins\Image\ImageProcessor;
use LizardsAndPumpkins\Image\ImageProcessorCollection;
use LizardsAndPumpkins\Image\ImageProcessingStrategySequence;
use LizardsAndPumpkins\LocalFilesystemStorageReader;
use LizardsAndPumpkins\LocalFilesystemStorageWriter;
use LizardsAndPumpkins\Log\Writer\FileLogMessageWriter;
use LizardsAndPumpkins\Log\WritingLoggerDecorator;
use LizardsAndPumpkins\Queue\File\FileQueue;
use LizardsAndPumpkins\SampleMasterFactory;
use LizardsAndPumpkins\SampleFactory;
use LizardsAndPumpkins\Website\WebsiteToCountryMap;

/**
 * @covers \LizardsAndPumpkins\SampleFactory
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\FilterNavigationPriceRangesBuilder
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderConfig
 * @uses   \LizardsAndPumpkins\ContentDelivery\Catalog\SortOrderDirection
 * @uses   \LizardsAndPumpkins\ContentDelivery\FacetFieldTransformation\FacetFieldTransformationRegistry
 * @uses   \LizardsAndPumpkins\FactoryTrait
 * @uses   \LizardsAndPumpkins\Log\InMemoryLogger
 * @uses   \LizardsAndPumpkins\Log\WritingLoggerDecorator
 * @uses   \LizardsAndPumpkins\Log\Writer\FileLogMessageWriter
 * @uses   \LizardsAndPumpkins\DataPool\KeyValue\File\FileKeyValueStore
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRange
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequest
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestRangedField
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestSimpleField
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FileSearchEngine
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder
 * @uses   \LizardsAndPumpkins\DataPool\UrlKeyStore\FileUrlKeyStore
 * @uses   \LizardsAndPumpkins\Image\ImageMagickInscribeStrategy
 * @uses   \LizardsAndPumpkins\Image\ImageProcessor
 * @uses   \LizardsAndPumpkins\Image\ImageProcessorCollection
 * @uses   \LizardsAndPumpkins\Image\ImageProcessingStrategySequence
 * @uses   \LizardsAndPumpkins\LocalFilesystemStorageReader
 * @uses   \LizardsAndPumpkins\LocalFilesystemStorageWriter
 * @uses   \LizardsAndPumpkins\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\EnvironmentConfigReader
 * @uses   \LizardsAndPumpkins\CommonFactory
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 */
class SampleFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SampleFactory
     */
    private $factory;

    protected function setUp()
    {
        $masterFactory = new SampleMasterFactory();
        $masterFactory->register(new CommonFactory());
        $this->factory = new SampleFactory();
        $masterFactory->register($this->factory);
    }

    protected function tearDown()
    {
        $keyValueStoragePath = sys_get_temp_dir() . '/lizards-and-pumpkins/key-value-store';
        if (file_exists($keyValueStoragePath)) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($keyValueStoragePath, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($iterator as $path) {
                $path->isDir() && !$path->isLink() ? rmdir($path->getPathname()) : unlink($path->getPathname());
            }
            rmdir($keyValueStoragePath);
        }
    }

    public function testFileKeyValueStoreIsReturned()
    {
        $this->assertInstanceOf(FileKeyValueStore::class, $this->factory->createKeyValueStore());
    }

    public function testFileSearchEngineIsReturned()
    {
        $this->assertInstanceOf(FileSearchEngine::class, $this->factory->createSearchEngine());
    }

    public function testInMemoryEventQueueIsReturned()
    {
        $this->assertInstanceOf(FileQueue::class, $this->factory->createEventQueue());
    }

    public function testInMemoryCommandQueueIsReturned()
    {
        $this->assertInstanceOf(FileQueue::class, $this->factory->createCommandQueue());
    }

    public function testWritingLoggerIsReturned()
    {
        $this->assertInstanceOf(WritingLoggerDecorator::class, $this->factory->createLogger());
    }

    public function testLogMessageWriterIsReturned()
    {
        $this->assertInstanceOf(FileLogMessageWriter::class, $this->factory->createLogMessageWriter());
    }

    public function testArrayOfSearchableAttributeCodesIsReturned()
    {
        $result = $this->factory->getSearchableAttributeCodes();

        $this->assertInternalType('array', $result);
        $this->assertContainsOnly('string', $result);
    }

    public function testProductListingFilterNavigationConfigIsInstanceOfFacetFilterRequest()
    {
        $result = $this->factory->getProductListingFilterNavigationConfig();
        $this->assertInstanceOf(FacetFilterRequest::class, $result);
    }

    public function testProductSearchResultsFilterNavigationConfigIsInstanceOfFacetFilterRequest()
    {
        $result = $this->factory->getProductSearchResultsFilterNavigationConfig();
        $this->assertInstanceOf(FacetFilterRequest::class, $result);
    }

    public function testImageProcessorCollectionIsReturned()
    {
        $this->assertInstanceOf(ImageProcessorCollection::class, $this->factory->createImageProcessorCollection());
    }

    public function testEnlargedImageProcessorIsReturned()
    {
        $this->assertInstanceOf(ImageProcessor::class, $this->factory->createOriginalImageProcessor());
    }

    public function testFileStorageReaderIsReturned()
    {
        $this->assertInstanceOf(
            LocalFilesystemStorageReader::class,
            $this->factory->createFileStorageReader()
        );
    }

    public function testFileStorageWriterIsReturned()
    {
        $this->assertInstanceOf(
            LocalFilesystemStorageWriter::class,
            $this->factory->createFileStorageWriter()
        );
    }

    public function testEnlargedImageProcessingStrategySequenceIsReturned()
    {
        $this->assertInstanceOf(
            ImageProcessingStrategySequence::class,
            $this->factory->createOriginalImageProcessingStrategySequence()
        );
    }

    public function testProductDetailsPageImageProcessorIsReturned()
    {
        $this->assertInstanceOf(ImageProcessor::class, $this->factory->createProductDetailsPageImageProcessor());
    }
    
    public function testProductDetailsPageImageProcessingStrategySequenceIsReturned()
    {
        $this->assertInstanceOf(
            ImageProcessingStrategySequence::class,
            $this->factory->createProductDetailsPageImageProcessingStrategySequence()
        );
    }

    public function testProductListingImageProcessorIsReturned()
    {
        $this->assertInstanceOf(ImageProcessor::class, $this->factory->createProductListingImageProcessor());
    }
    
    public function testProductListingImageProcessingStrategySequenceIsReturned()
    {
        $this->assertInstanceOf(
            ImageProcessingStrategySequence::class,
            $this->factory->createProductListingImageProcessingStrategySequence()
        );
    }

    public function testGalleyThumbnailImageProcessorIsReturned()
    {
        $this->assertInstanceOf(ImageProcessor::class, $this->factory->createGalleyThumbnailImageProcessor());
    }
    
    public function testGalleyThumbnailImageProcessingStrategySequenceIsReturned()
    {
        $this->assertInstanceOf(
            ImageProcessingStrategySequence::class,
            $this->factory->createGalleyThumbnailImageProcessingStrategySequence()
        );
    }

    public function testFileUrlKeyStoreIsReturned()
    {
        $this->assertInstanceOf(FileUrlKeyStore::class, $this->factory->createUrlKeyStore());
    }

    public function testItReturnsAnExistingDirectoryAsTheFileStorageBasePathConfig()
    {
        $fileStorageBasePath = $this->factory->getFileStorageBasePathConfig();
        $this->assertInternalType('string', $fileStorageBasePath);
        $this->assertFileExists($fileStorageBasePath);
        $this->assertTrue(is_dir($fileStorageBasePath));
    }

    public function testSameInstanceOfProductListingSortOrderConfigIsReturnedOnMultipleCalls()
    {
        $this->assertContainsOnly(SortOrderConfig::class, $this->factory->getProductListingSortOrderConfig());
        $this->assertSame(
            $this->factory->getProductListingSortOrderConfig(),
            $this->factory->getProductListingSortOrderConfig()
        );
    }

    public function testSameInstanceOfProductSearchSortOrderConfigIsReturnedOnMultipleCalls()
    {
        $this->assertContainsOnly(SortOrderConfig::class, $this->factory->getProductSearchSortOrderConfig());
        $this->assertSame(
            $this->factory->getProductSearchSortOrderConfig(),
            $this->factory->getProductSearchSortOrderConfig()
        );
    }

    public function testSameInstanceOfProductSearchAutosuggestionSortOrderConfigIsReturnedOnMultipleCalls()
    {
        $this->assertInstanceOf(
            SortOrderConfig::class,
            $this->factory->getProductSearchAutosuggestionSortOrderConfig()
        );
        $this->assertSame(
            $this->factory->getProductSearchAutosuggestionSortOrderConfig(),
            $this->factory->getProductSearchAutosuggestionSortOrderConfig()
        );
    }

    public function testItReturnsAWebsiteToCountryMapInstance()
    {
        $this->assertInstanceOf(WebsiteToCountryMap::class, $this->factory->createWebsiteToCountryMap());
    }
}
