<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\DataPool\DataPoolReader;
use Brera\Logger;
use Brera\Product\Spies\ProductListingRequestHandlerSpy;
use Brera\SnippetKeyGeneratorLocator;

/**
 * @covers \Brera\Product\ProductListingRequestHandler
 * @uses \Brera\Http\AbstractHttpRequestHandler
 * @uses \Brera\Product\ProductListingMetaInfoSnippetContent
 */
class ProductListingRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $urlPathFixture = 'dummy-url-path';

    /**
     * @var string
     */
    private $rootSnippetCode = 'root-snippet-code';
    
    /**
     * @var string[]
     */
    private $testSelectionCriteria = ['test-attribute' => 'test-value'];

    /**
     * @var ProductListingRequestHandlerSpy
     */
    private $requestHandlerSpy;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @var SnippetKeyGeneratorLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSnippetKeyGeneratorLocator;

    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolReader;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubLogger;

    /**
     * @param string[] $testMatchingProductIds
     */
    private function setMatchingProductsFixture(array $testMatchingProductIds)
    {
        $this->mockDataPoolReader->expects($this->once())->method('getProductIdsMatchingCriteria')
            ->with($this->testSelectionCriteria)->willReturn($testMatchingProductIds);
    }

    /**
     * @param string[] $productInListingSnippetFixture
     */
    private function setProductListSnippetsInKeyValueStorageFixture(array $productInListingSnippetFixture)
    {
        $this->mockDataPoolReader->expects($this->once())->method('getSnippets')
            ->willReturn($productInListingSnippetFixture);
    }

    /**
     * @return string
     */
    private function getStubPageMetaInfoJson()
    {
        $pageMetaInfo = [
            ProductListingMetaInfoSnippetContent::KEY_CRITERIA => $this->testSelectionCriteria,
            ProductDetailPageMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE => $this->rootSnippetCode,
            ProductDetailPageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES => []
        ];
        return json_encode($pageMetaInfo);
    }

    /**
     * @return SnippetKeyGeneratorLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createKeyGeneratorLocatorFake()
    {
        $fixedSnippetKeyGeneratorFactory = function ($snippetCode) {
            $stubSnippetKeyGenerator = $this->getMock(ProductSnippetKeyGenerator::class, [], [], '', false);
            $stubSnippetKeyGenerator->expects($this->any())->method('getKeyForContext')
                ->willReturnCallback(function (Context $context, array $data = []) use ($snippetCode) {
                    return $snippetCode . '_' . reset($data);
                });
            return $stubSnippetKeyGenerator;
        };
        $stubSnippetKeyGeneratorLocator = $this->getMock(SnippetKeyGeneratorLocator::class);
        $stubSnippetKeyGeneratorLocator->expects($this->any())->method('getKeyGeneratorForSnippetCode')
            ->willReturnCallback($fixedSnippetKeyGeneratorFactory);
        return $stubSnippetKeyGeneratorLocator;
    }

    protected function setUp()
    {
        $this->stubContext = $this->getMock(Context::class);
        $this->stubSnippetKeyGeneratorLocator = $this->createKeyGeneratorLocatorFake();
        $this->mockDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);
        $this->stubLogger = $this->getMock(Logger::class);
        $this->requestHandlerSpy = new ProductListingRequestHandlerSpy(
            $this->urlPathFixture,
            $this->stubContext,
            $this->stubSnippetKeyGeneratorLocator,
            $this->mockDataPoolReader,
            $this->stubLogger
        );
    }

    /**
     * @test
     */
    public function itShouldLoadTheProductSnippetsFromTheQueryResult()
    {
        $testProductId = 'id55';
        $expectedSnippetKey = ProductInListingInContextSnippetRenderer::CODE . '_' . $testProductId;

        $this->requestHandlerSpy->testCreatePageMetaInfoInstance($this->getStubPageMetaInfoJson());
        $this->setMatchingProductsFixture([$testProductId]);

        $this->mockDataPoolReader->expects($this->once())->method('getSnippets')
            ->with([$expectedSnippetKey])
            ->willReturn(['dummy_id55' => 'Dummy Content']);

        $this->requestHandlerSpy->testAddPageSpecificAdditionalSnippetsHook();
        
    }

    /**
     * @test
     */
    public function itShouldMapTheProductInListSnippetKeysToIncrementingSnippetCodes()
    {
        $testMatchingProductIds = ['id1', 'id2', 'id3'];
        $productInListingSnippetFixture = [
            'product_in_listing_id1' => 'Product 1 in Listing Snippet',
            'product_in_listing_id2' => 'Product 2 in Listing Snippet',
            'product_in_listing_id3' => 'Product 3 in Listing Snippet',
        ];
        $expectedSnippetCodeToKeyMap = [
            'product_1' => 'product_in_listing_id1',
            'product_2' => 'product_in_listing_id2',
            'product_3' => 'product_in_listing_id3',
        ];

        $this->requestHandlerSpy->testCreatePageMetaInfoInstance($this->getStubPageMetaInfoJson());
        
        $this->setMatchingProductsFixture($testMatchingProductIds);
        $this->mockDataPoolReader->expects($this->once())->method('getSnippets')
            ->with(array_keys($productInListingSnippetFixture))
            ->willReturn($productInListingSnippetFixture);


        $this->requestHandlerSpy->testAddPageSpecificAdditionalSnippetsHook();

        $this->assertAttributeEquals($expectedSnippetCodeToKeyMap, 'snippetCodeToKeyMap', $this->requestHandlerSpy);
    }

    /**
     * @test
     */
    public function itShouldReturnTheInjectedPageMetaInfoSnippetKey()
    {
        $this->assertSame($this->urlPathFixture, $this->requestHandlerSpy->testGetPageMetaInfoSnippetKey());
    }

    /**
     * @test
     */
    public function itShouldDelegateToASnippetKeyGeneratorToFetchASnippetKey()
    {
        $testSnippetCode = 'test-snippet-code';
        $expectedSnippetKeyFixture = 'test-snippet-key';
        
        $stubKeyGenerator = $this->getMock(ProductSnippetKeyGenerator::class, [], [], '', false);
        $stubKeyGenerator->expects($this->once())->method('getKeyForContext')
            ->with($this->isInstanceOf(Context::class), $this->arrayHasKey('selection_criteria'))
            ->willReturn($expectedSnippetKeyFixture);
        $stubSnippetKeyGeneratorLocator = $this->getMock(SnippetKeyGeneratorLocator::class);
        $stubSnippetKeyGeneratorLocator->expects($this->once())->method('getKeyGeneratorForSnippetCode')
            ->with($testSnippetCode)->willReturn($stubKeyGenerator);
        
        $requestHandlerSpy = new ProductListingRequestHandlerSpy(
            $this->urlPathFixture,
            $this->stubContext,
            $stubSnippetKeyGeneratorLocator,
            $this->mockDataPoolReader,
            $this->stubLogger
        );
        $requestHandlerSpy->testCreatePageMetaInfoInstance($this->getStubPageMetaInfoJson());

        $result = $requestHandlerSpy->testGetSnippetKey($testSnippetCode);
        $this->assertSame($expectedSnippetKeyFixture, $result);
    }

    /**
     * @test
     */
    public function itShouldIncludeSnippetCodeSelectionCriteriaAndContextIdInErrorMessage()
    {
        $snippetKey = 'test-snippet-key';
        $criteria = implode('|', $this->testSelectionCriteria);
        $contextId = 'test-context-id';

        $this->requestHandlerSpy->testCreatePageMetaInfoInstance($this->getStubPageMetaInfoJson());

        $this->stubContext->expects($this->any())->method('getId')->willReturn($contextId);

        $result = $this->requestHandlerSpy->testFormatSnippetNotAvailableErrorMessage($snippetKey);
        $this->assertContains($snippetKey, $result);
        $this->assertContains($criteria, $result);
        $this->assertContains($contextId, $result);
    }

    /**
     * @test
     */
    public function itShouldReturnTheInjectedDataPoolReader()
    {
        $this->assertSame($this->mockDataPoolReader, $this->requestHandlerSpy->testGetDataPoolReader());
    }

    /**
     * @test
     */
    public function itShouldReturnTheInjectedLogger()
    {
        $this->assertSame($this->stubLogger, $this->requestHandlerSpy->testGetLogger());
    }
}
