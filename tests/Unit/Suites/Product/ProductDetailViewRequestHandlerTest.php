<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\DataPool\DataPoolReader;
use Brera\Logger;
use Brera\PageMetaInfoSnippetContent;
use Brera\Product\Spies\ProductDetailViewRequestHandlerSpy;
use Brera\SnippetKeyGeneratorLocator;

/**
 * @covers \Brera\Product\ProductDetailViewRequestHandler
 * @uses   \Brera\Product\ProductDetailPageMetaInfoSnippetContent
 */
class ProductDetailViewRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    private $urlPathKeyFixture = 'dummy-url-key';

    /**
     * @var ProductDetailViewRequestHandlerSpy
     */
    private $requestHandlerSpy;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @var SnippetKeyGeneratorLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetKeyGeneratorLocator;

    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubDataPoolReader;

    /**
     * @var Logger|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubLogger;

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $fakeKeyGeneratorLocator
     * @return SnippetKeyGeneratorLocator|\PHPUnit_Framework_MockObject_MockObject
     */
    private function fakeSnippetKeyGeneratorLocator(\PHPUnit_Framework_MockObject_MockObject $fakeKeyGeneratorLocator)
    {
        $fixedKeyGeneratorMockFactory = function ($snippetCode) {
            $keyGenerator = $this->getMock(ProductSnippetKeyGenerator::class, [], [], '', false);
            $keyGenerator->expects($this->once())->method('getKeyForContext')->willReturn($snippetCode);
            return $keyGenerator;
        };
        $fakeKeyGeneratorLocator->expects($this->once())->method('getKeyGeneratorForSnippetCode')
            ->willReturnCallback($fixedKeyGeneratorMockFactory);
        return $fakeKeyGeneratorLocator;
    }

    /**
     * @param string $productId
     * @return string
     */
    private function getStubMetaInfoJsonForProductId($productId)
    {
        $stubPageMetaInfo = [
            ProductDetailPageMetaInfoSnippetContent::KEY_PRODUCT_ID => $productId,
            PageMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE => 'dummy-root-snippet',
            PageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES => []
        ];
        return json_encode($stubPageMetaInfo);
    }

    protected function setUp()
    {
        $this->stubContext = $this->getMock(Context::class);
        $this->mockSnippetKeyGeneratorLocator = $this->getMock(SnippetKeyGeneratorLocator::class);
        $this->stubDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);
        $this->stubLogger = $this->getMock(Logger::class);
        $this->requestHandlerSpy = new ProductDetailViewRequestHandlerSpy(
            $this->urlPathKeyFixture,
            $this->stubContext,
            $this->mockSnippetKeyGeneratorLocator,
            $this->stubDataPoolReader,
            $this->stubLogger
        );
    }

    /**
     * @test
     */
    public function itShouldReturnProductDetailPageMetaInfoContent()
    {
        $json = $this->getStubMetaInfoJsonForProductId('id55');
        $result = $this->requestHandlerSpy->testCreatePageMetaInfoInstance($json);
        $this->assertInstanceOf(ProductDetailPageMetaInfoSnippetContent::class, $result);
    }

    /**
     * @test
     */
    public function itShouldDelegateToTheSnippetKeyGeneratorLocatorToFetchASnippetKey()
    {
        $this->fakeSnippetKeyGeneratorLocator($this->mockSnippetKeyGeneratorLocator);
        $this->requestHandlerSpy->testGetSnippetKey('test');
    }

    /**
     * @test
     */
    public function itShouldPrefixThePageMetaInfoSnippetKeyWithTheProductDetailViewSnippetCode()
    {
        $expected = ProductDetailViewInContextSnippetRenderer::CODE . '_' . $this->urlPathKeyFixture;
        $this->assertSame($expected, $this->requestHandlerSpy->testGetPageMetaInfoSnippetKey());
    }

    /**
     * @test
     */
    public function itShouldIncludeSnippetCodeProductIdAndContextIdInErrorMessage()
    {
        $snippetKey = 'test-snippet-key';
        $productId = 'id55';
        $contextId = 'test-context-id';

        $json = $this->getStubMetaInfoJsonForProductId($productId);
        $this->requestHandlerSpy->testCreatePageMetaInfoInstance($json);
        
        $this->stubContext->expects($this->any())->method('getId')->willReturn($contextId);
        
        $result = $this->requestHandlerSpy->testFormatSnippetNotAvailableErrorMessage($snippetKey);
        $this->assertContains($snippetKey, $result);
        $this->assertContains($productId, $result);
        $this->assertContains($contextId, $result);
    }

    /**
     * @test
     */
    public function itShouldReturnTheInjectedDataPoolReader()
    {
        $this->assertSame($this->stubDataPoolReader, $this->requestHandlerSpy->testGetDataPoolReader());
    }

    /**
     * @test
     */
    public function itShouldReturnTheInjectedLogger()
    {
        $this->assertSame($this->stubLogger, $this->requestHandlerSpy->testGetLogger());
    }
}
