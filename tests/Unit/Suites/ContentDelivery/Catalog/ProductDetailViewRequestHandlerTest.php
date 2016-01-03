<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\KeyValue\Exception\KeyNotFoundException;
use LizardsAndPumpkins\DefaultHttpResponse;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestHandler;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Http\Exception\UnableToHandleRequestException;
use LizardsAndPumpkins\ContentDelivery\PageBuilder;
use LizardsAndPumpkins\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductDetailPageMetaInfoSnippetContent;
use LizardsAndPumpkins\SnippetKeyGenerator;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\ProductDetailViewRequestHandler
 * @uses   \LizardsAndPumpkins\Product\ProductDetailPageMetaInfoSnippetContent
 */
class ProductDetailViewRequestHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductDetailViewRequestHandler
     */
    private $requestHandler;

    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDataPoolReader;

    /**
     * @var string
     */
    private $dummyMetaInfoKey = 'stub-meta-info-key';

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @var string
     */
    private $dummyMetaInfoSnippetJson;

    /**
     * @var PageBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubPageBuilder;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    /**
     * @var string
     */
    private $testProductId = '123';

    /**
     * @var SnippetKeyGenerator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSnippetKeyGenerator;

    /**
     * @return string
     */
    private function createProductDetailPageMetaInfoContentJson()
    {
        return json_encode(ProductDetailPageMetaInfoSnippetContent::create(
            $this->testProductId,
            'root-snippet-code',
            ['child-snippet1']
        )->getInfo());
    }

    protected function setUp()
    {
        $this->dummyMetaInfoSnippetJson = $this->createProductDetailPageMetaInfoContentJson();

        $this->mockDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);
        $this->stubContext = $this->getMock(Context::class);
        $this->stubPageBuilder = $this->getMock(PageBuilder::class, [], [], '', false);

        $this->stubSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class);

        $this->requestHandler = new ProductDetailViewRequestHandler(
            $this->stubContext,
            $this->mockDataPoolReader,
            $this->stubPageBuilder,
            $this->stubSnippetKeyGenerator
        );

        $stubUrl = $this->getMock(HttpUrl::class, [], [], '', false);

        $this->stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $this->stubRequest->method('getUrl')->willReturn($stubUrl);
    }

    public function testRequestHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(HttpRequestHandler::class, $this->requestHandler);
    }

    public function testFalseIsReturnedIfPageMetaInfoContentSnippetCanNotBeLoaded()
    {
        $exception = new KeyNotFoundException();
        $this->mockDataPoolReader->method('getSnippet')->willThrowException($exception);
        $this->assertFalse($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testTrueIsReturnedIfPageMetaInfoContentSnippetCanBeLoaded()
    {
        $this->stubSnippetKeyGenerator->method('getKeyForContext')->willReturn($this->dummyMetaInfoKey);
        $this->mockDataPoolReader->method('getSnippet')->willReturnMap([
            [$this->dummyMetaInfoKey, $this->dummyMetaInfoSnippetJson]
        ]);
        $this->assertTrue($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testExceptionIsThrownIfProcessWithoutMetaInfoContentIsCalled()
    {
        $this->setExpectedException(UnableToHandleRequestException::class);
        $this->requestHandler->process($this->stubRequest);
    }

    public function testPageMetaInfoSnippetIsCreated()
    {
        $this->stubSnippetKeyGenerator->method('getKeyForContext')->willReturn($this->dummyMetaInfoKey);
        $this->mockDataPoolReader->method('getSnippet')->willReturnMap([
            [$this->dummyMetaInfoKey, $this->dummyMetaInfoSnippetJson]
        ]);

        $this->requestHandler->process($this->stubRequest);

        $this->assertAttributeInstanceOf(
            ProductDetailPageMetaInfoSnippetContent::class,
            'pageMetaInfo',
            $this->requestHandler
        );
    }

    public function testPageIsReturned()
    {
        $this->stubSnippetKeyGenerator->method('getKeyForContext')->willReturn($this->dummyMetaInfoKey);
        $this->mockDataPoolReader->method('getSnippet')->willReturnMap([
            [$this->dummyMetaInfoKey, $this->dummyMetaInfoSnippetJson]
        ]);
        $this->stubPageBuilder->method('buildPage')->with(
            $this->anything(),
            $this->anything(),
            [Product::ID => $this->testProductId]
        )->willReturn($this->getMock(DefaultHttpResponse::class, [], [], '', false));

        $this->assertInstanceOf(DefaultHttpResponse::class, $this->requestHandler->process($this->stubRequest));
    }

    public function testItHandlesDifferentRequestsIndependently()
    {
        $urlKeyA = 'A.html';
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubRequestA */
        $stubRequestA = $this->getMock(HttpRequest::class, [], [], '', false);
        $stubRequestA->method('getUrlPathRelativeToWebFront')->willReturn($urlKeyA);

        $urlKeyB = 'B.html';
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubRequestB */
        $stubRequestB = $this->getMock(HttpRequest::class, [], [], '', false);
        $stubRequestB->method('getUrlPathRelativeToWebFront')->willReturn($urlKeyB);

        $requestAMetaInfoSnippetKey = 'A';
        $requestBMetaInfoSnippetKey = 'B';

        $this->stubSnippetKeyGenerator->method('getKeyForContext')->willReturnMap([
            [$this->stubContext, [PageMetaInfoSnippetContent::URL_KEY => $urlKeyA], $requestAMetaInfoSnippetKey],
            [$this->stubContext, [PageMetaInfoSnippetContent::URL_KEY => $urlKeyB], $requestBMetaInfoSnippetKey],
        ]);

        $this->mockDataPoolReader->method('getSnippet')->willReturnMap([
            [$requestAMetaInfoSnippetKey, $this->createProductDetailPageMetaInfoContentJson()],
            [$requestBMetaInfoSnippetKey, ''],
        ]);

        $this->assertTrue($this->requestHandler->canProcess($stubRequestA));
        $this->assertFalse($this->requestHandler->canProcess($stubRequestB));
    }
}
