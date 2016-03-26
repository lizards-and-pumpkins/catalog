<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Import\Tax\TaxableCountries;
use LizardsAndPumpkins\ProductListing\ContentDelivery\ProductsPerPage;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\SearchFieldToRequestParamMap;
use LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderConfig;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\KeyValue\File\FileKeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestField;
use LizardsAndPumpkins\DataPool\SearchEngine\Filesystem\FileSearchEngine;
use LizardsAndPumpkins\DataPool\UrlKeyStore\FileUrlKeyStore;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessor;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessorCollection;
use LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessingStrategySequence;
use LizardsAndPumpkins\Logging\Writer\CompositeLogMessageWriter;
use LizardsAndPumpkins\Logging\Writer\FileLogMessageWriter;
use LizardsAndPumpkins\Logging\WritingLoggerDecorator;
use LizardsAndPumpkins\Import\Product\Image\TwentyOneRunProductImageFileLocator;
use LizardsAndPumpkins\Import\Tax\TaxServiceLocator;
use LizardsAndPumpkins\ProductListing\Import\TwentyOneRunProductListingTitleSnippetRenderer;
use LizardsAndPumpkins\Import\Product\View\ProductViewLocator;
use LizardsAndPumpkins\Queue\File\FileQueue;
use LizardsAndPumpkins\Import\ImageStorage\ImageStorage;
use LizardsAndPumpkins\Context\Website\WebsiteToCountryMap;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\SampleMasterFactory;
use LizardsAndPumpkins\Util\Factory\TwentyOneRunFactory;
use LizardsAndPumpkins\Util\FileSystem\LocalFilesystemStorageReader;
use LizardsAndPumpkins\Util\FileSystem\LocalFilesystemStorageWriter;

/**
 * @covers \LizardsAndPumpkins\Util\Factory\TwentyOneRunFactory
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FilterNavigationPriceRangesBuilder
 * @uses   \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductsPerPage
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderConfig
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderDirection
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\FacetFieldTransformationRegistry
 * @uses   \LizardsAndPumpkins\ProductSearch\ContentDelivery\SearchFieldToRequestParamMap
 * @uses   \LizardsAndPumpkins\Context\Country\ContextCountry
 * @uses   \LizardsAndPumpkins\Context\DataVersion\ContextVersion
 * @uses   \LizardsAndPumpkins\Context\Website\ContextWebsite
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\Util\Factory\FactoryTrait
 * @uses   \LizardsAndPumpkins\Logging\InMemoryLogger
 * @uses   \LizardsAndPumpkins\Logging\WritingLoggerDecorator
 * @uses   \LizardsAndPumpkins\Logging\Writer\FileLogMessageWriter
 * @uses   \LizardsAndPumpkins\DataPool\DataPoolReader
 * @uses   \LizardsAndPumpkins\DataPool\KeyValue\File\FileKeyValueStore
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRange
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFiltersToIncludeInResult
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestRangedField
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRequestSimpleField
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\Filesystem\FileSearchEngine
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\UrlKeyStore\FileUrlKeyStore
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\DataPool\KeyGenerator\GenericSnippetKeyGenerator
 * @uses   \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageMagick\ImageMagickInscribeStrategy
 * @uses   \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessor
 * @uses   \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessorCollection
 * @uses   \LizardsAndPumpkins\Import\ImageStorage\ImageProcessing\ImageProcessingStrategySequence
 * @uses   \LizardsAndPumpkins\Util\FileSystem\LocalFilesystemStorageReader
 * @uses   \LizardsAndPumpkins\Util\FileSystem\LocalFilesystemStorageWriter
 * @uses   \LizardsAndPumpkins\Util\Factory\MasterFactoryTrait
 * @uses   \LizardsAndPumpkins\Util\Config\EnvironmentConfigReader
 * @uses   \LizardsAndPumpkins\Util\Factory\CommonFactory
 * @uses   \LizardsAndPumpkins\Import\Product\View\TwentyOneRunProductViewLocator
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Import\Product\Image\TwentyOneRunProductImageFileLocator
 * @uses   \LizardsAndPumpkins\ProductListing\Import\TwentyOneRunProductListingTitleSnippetRenderer
 * @uses   \LizardsAndPumpkins\Import\ImageStorage\MediaDirectoryBaseUrlBuilder
 * @uses   \LizardsAndPumpkins\Import\ImageStorage\FilesystemImageStorage
 * @uses   \LizardsAndPumpkins\Import\FileStorage\FilesystemFileStorage
 * @uses   \LizardsAndPumpkins\Context\BaseUrl\WebsiteBaseUrlBuilder
 * @uses   \LizardsAndPumpkins\Import\Tax\TwentyOneRunTaxableCountries
 * @uses   \LizardsAndPumpkins\Context\Website\ConfigurableHostToWebsiteMap
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\FacetFieldTransformation\CurrencyPriceRangeTransformation
 */
class TwentyOneRunFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var TwentyOneRunFactory
     */
    private $factory;

    /**
     * @param FacetFilterRequestField[] $facetFilterFields
     * @return string[]
     */
    private function getFacetCodes(FacetFilterRequestField ...$facetFilterFields)
    {
        return array_map(function (FacetFilterRequestField $field) {
            return (string) $field->getAttributeCode();
        }, $facetFilterFields);
    }

    /**
     * @param mixed $newPath
     * @return mixed
     */
    private function changeFileLogPathInEnvironmentConfig($newPath)
    {
        $oldState = null;

        if (isset($_SERVER['LP_LOG_FILE_PATH'])) {
            $oldState = $_SERVER['LP_LOG_FILE_PATH'];
            unset($_SERVER['LP_LOG_FILE_PATH']);
        }

        if (null !== $newPath) {
            $_SERVER['LP_LOG_FILE_PATH'] = $newPath;
        }

        return $oldState;
    }

    protected function setUp()
    {
        $masterFactory = new SampleMasterFactory();
        $masterFactory->register(new CommonFactory());
        $this->factory = new TwentyOneRunFactory();
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
        $this->assertInstanceOf(CompositeLogMessageWriter::class, $this->factory->createLogMessageWriter());
    }

    public function testArrayOfSearchableAttributeCodesIsReturned()
    {
        $result = $this->factory->getSearchableAttributeCodes();

        $this->assertInternalType('array', $result);
        $this->assertContainsOnly('string', $result);
    }

    /**
     * @param string $fieldName
     * @dataProvider facetFieldsToIncludeInResultProvider
     */
    public function testItReturnsAListOfFacetFilterRequestFieldsForTheProductListings($fieldName)
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);
        $stubContext->method('getValue')->willReturn('DE');
        $fieldCodes = $this->getFacetCodes(...$this->factory->getProductListingFacetFilterRequestFields($stubContext));
        $this->assertContains($fieldName, $fieldCodes);
    }

    public function testItInjectsThePriceAfterTheBrandFacetForProductListings()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);
        $stubContext->method('getValue')->willReturn('DE');
        $fieldCodes = $this->getFacetCodes(...$this->factory->getProductListingFacetFilterRequestFields($stubContext));
        $brandPosition = array_search('brand', $fieldCodes, true);
        $this->assertEquals('price_incl_tax_de', $fieldCodes[$brandPosition + 1]);
    }

    /**
     * @param string $fieldName
     * @dataProvider facetFieldsToIncludeInResultProvider
     */
    public function testItReturnsAListOfFacetFilterRequestFieldsForTheSearchResults($fieldName)
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);
        $stubContext->method('getValue')->willReturn('DE');
        $fieldCodes = $this->getFacetCodes(...$this->factory->getProductSearchFacetFilterRequestFields($stubContext));
        $this->assertContains($fieldName, $fieldCodes);
    }

    public function testItInjectsThePriceAfterTheBrandFacetForSearchListings()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);
        $stubContext->method('getValue')->willReturn('DE');
        $fieldCodes = $this->getFacetCodes(...$this->factory->getProductSearchFacetFilterRequestFields($stubContext));
        $brandPosition = array_search('brand', $fieldCodes, true);
        $this->assertEquals('price_incl_tax_de', $fieldCodes[$brandPosition + 1]);
    }

    /**
     * @param string $fieldName
     * @dataProvider facetFieldsToIndexProvider
     */
    public function testItReturnsAListOfFacetFilterCodesForSearchDocuments($fieldName)
    {
        $this->assertContains($fieldName, $this->factory->getFacetFilterRequestFieldCodesForSearchDocuments());
    }

    /**
     * @return array[]
     */
    public function facetFieldsToIncludeInResultProvider()
    {
        return array_merge($this->facetFieldsToIndexProvider(), [['price_incl_tax_de']]);
    }

    /**
     * @return array[]
     */
    public function facetFieldsToIndexProvider()
    {
        return [
            ['gender'],
            ['product_group'],
            ['style'],
            ['brand'],
            ['series'],
            ['size'],
            ['color'],
        ];
    }

    public function testArrayOfAdditionalAttributeCodesForSearchEngineIsReturned()
    {
        $result = $this->factory->getAdditionalAttributesForSearchIndex();

        $this->assertInternalType('array', $result);
        $this->assertContainsOnly('string', $result);
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
        $result = $this->factory->getProductListingSortOrderConfig();

        $this->assertContainsOnly(SortOrderConfig::class, $result);
        $this->assertSame($result, $this->factory->getProductListingSortOrderConfig());
    }

    public function testSameInstanceOfProductSearchSortOrderConfigIsReturnedOnMultipleCalls()
    {
        $result = $this->factory->getProductSearchSortOrderConfig();

        $this->assertContainsOnly(SortOrderConfig::class, $result);
        $this->assertSame($result, $this->factory->getProductSearchSortOrderConfig());
    }

    public function testSameInstanceOfProductSearchAutosuggestionSortOrderConfigIsReturnedOnMultipleCalls()
    {
        $result = $this->factory->getProductSearchAutosuggestionSortOrderConfig();

        $this->assertInstanceOf(SortOrderConfig::class, $result);
        $this->assertSame($result, $this->factory->getProductSearchAutosuggestionSortOrderConfig());
    }

    public function testSameInstanceOfProductsPerPageIsReturned()
    {
        $result1 = $this->factory->getProductsPerPageConfig();
        $result2 = $this->factory->getProductsPerPageConfig();

        $this->assertInstanceOf(ProductsPerPage::class, $result1);
        $this->assertSame($result1, $result2);
    }

    public function testItReturnsAWebsiteToCountryMapInstance()
    {
        $this->assertInstanceOf(WebsiteToCountryMap::class, $this->factory->createWebsiteToCountryMap());
    }

    public function testItReturnsATaxableCountryInstance()
    {
        $this->assertInstanceOf(TaxableCountries::class, $this->factory->createTaxableCountries());
    }

    public function testItReturnsATaxServiceLocator()
    {
        $this->assertInstanceOf(TaxServiceLocator::class, $this->factory->createTaxServiceLocator());
    }

    public function testProductViewLocatorIsReturned()
    {
        $this->assertInstanceOf(ProductViewLocator::class, $this->factory->createProductViewLocator());
    }

    public function testItReturnsAProductImageFileLocatorInstance()
    {
        $result = $this->factory->createProductImageFileLocator();
        $this->assertInstanceOf(TwentyOneRunProductImageFileLocator::class, $result);
    }

    public function testItReturnsAnImageStorage()
    {
        $this->assertInstanceOf(ImageStorage::class, $this->factory->createImageStorage());
    }

    public function testItReturnsASearchFieldToRequestParamMap()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);
        $result = $this->factory->createSearchFieldToRequestParamMap($stubContext);
        $this->assertInstanceOf(SearchFieldToRequestParamMap::class, $result);
    }

    public function testItReturnsThePriceFacetFieldName()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);
        $stubContext->method('getValue')->willReturn('DE');
        $this->assertSame('price_incl_tax_de', $this->factory->getPriceFacetFieldNameForContext($stubContext));
    }

    public function testDefaultFileLogPathIsReturned()
    {
        $expectedPath = preg_replace('/tests\/Unit\/Suites/', 'src/Util/Factory/', __DIR__ . '../../../log/system.log');
        $this->assertSame($expectedPath, $this->factory->getLogFilePathConfig());
    }

    public function testFileLogPathStoredInEnvironmentIsReturned()
    {
        $expectedPath = 'foo';
        $oldPath = $this->changeFileLogPathInEnvironmentConfig($expectedPath);

        $this->assertSame($expectedPath, $this->factory->getLogFilePathConfig());

        $this->changeFileLogPathInEnvironmentConfig($oldPath);
    }

    public function testTwentyOneRunProductListingTitleSnippetRendererIsReturned()
    {
        $result = $this->factory->createProductListingTitleSnippetRenderer();
        $this->assertInstanceOf(TwentyOneRunProductListingTitleSnippetRenderer::class, $result);
    }
}
