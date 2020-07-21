<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductDetail;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\GenericHttpResponse;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageBuilder;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Http\Routing\HttpRequestHandler;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Translation\Translator;
use LizardsAndPumpkins\Translation\TranslatorRegistry;
use PHPUnit\Framework\MockObject\Invocation\ObjectInvocation;
use PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductDetail\ProductDetailViewRequestHandler
 * @uses   \LizardsAndPumpkins\ProductDetail\ProductDetailPageMetaInfoSnippetContent
 * @uses   \LizardsAndPumpkins\Util\SnippetCodeValidator
 * @uses   \LizardsAndPumpkins\Http\HttpUrl
 */
class ProductDetailViewRequestHandlerTest extends TestCase
{
    /**
     * @var ProductDetailViewRequestHandler
     */
    private $requestHandler;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @var PageBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockPageBuilder;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    /**
     * @var Translator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubTranslator;

    /**
     * @var AnyInvokedCount
     */
    private $addSnippetsToPageSpy;

    private function assertDynamicSnippetWasAddedToPageBuilder(string $snippetCode, string $snippetValue)
    {
        $numberOfTimesSnippetWasAddedToPageBuilder = array_sum(
            array_map(function (ObjectInvocation $invocation) use ($snippetCode, $snippetValue) {
                return (int) ([$snippetCode => $snippetCode] === $invocation->getParameters()[0] &&
                              [$snippetCode => $snippetValue] === $invocation->getParameters()[1]);
            }, $this->addSnippetsToPageSpy->getInvocations())
        );

        $this->assertEquals(1, $numberOfTimesSnippetWasAddedToPageBuilder, sprintf(
            'Failed to assert "%s" snippet with "%s" value was added to page builder.',
            $snippetCode,
            $snippetValue
        ));
    }

    final protected function setUp()
    {
        $pageMeta = [
            ProductDetailPageMetaInfoSnippetContent::KEY_PRODUCT_ID => 'foo',
            PageMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE => 'root-snippet-code',
            PageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES => ['child-snippet1'],
            PageMetaInfoSnippetContent::KEY_CONTAINER_SNIPPETS => [],
            PageMetaInfoSnippetContent::KEY_PAGE_SPECIFIC_DATA => [],
        ];

        $this->stubContext = $this->createMock(Context::class);
        $this->mockPageBuilder = $this->createMock(PageBuilder::class);

        $this->addSnippetsToPageSpy = new AnyInvokedCount();
        $this->mockPageBuilder->expects($this->addSnippetsToPageSpy)->method('addSnippetsToPage');

        $this->stubTranslator = $this->createMock(Translator::class);

        /** @var TranslatorRegistry|\PHPUnit_Framework_MockObject_MockObject $stubTranslatorRegistry */
        $stubTranslatorRegistry = $this->createMock(TranslatorRegistry::class);
        $stubTranslatorRegistry->method('getTranslator')->willReturn($this->stubTranslator);

        $this->requestHandler = new ProductDetailViewRequestHandler(
            $this->stubContext,
            $this->mockPageBuilder,
            $stubTranslatorRegistry,
            $pageMeta
        );

        $stubUrl = $this->createMock(HttpUrl::class);

        $this->stubRequest = $this->createMock(HttpRequest::class);
        $this->stubRequest->method('getUrl')->willReturn($stubUrl);
    }

    public function testRequestHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(HttpRequestHandler::class, $this->requestHandler);
    }

    public function testCanProcessAnyRequest()
    {
        $this->assertTrue($this->requestHandler->canProcess($this->stubRequest));
    }

    public function testPageIsReturned()
    {
        $this->mockPageBuilder->method('buildPage')->with(
            $this->anything(),
            $this->anything(),
            $this->isType('array')
        )->willReturn($this->createMock(GenericHttpResponse::class));

        $this->assertInstanceOf(GenericHttpResponse::class, $this->requestHandler->process($this->stubRequest));
    }

    public function testTranslationsAreAddedToPageBuilder()
    {
        $translations = ['foo' => 'bar'];

        $this->stubTranslator->method('jsonSerialize')->willReturn($translations);

        $this->mockPageBuilder->method('buildPage')
            ->willReturn($this->createMock(GenericHttpResponse::class));

        $this->requestHandler->process($this->stubRequest);

        $snippetCode = 'translations';
        $this->assertDynamicSnippetWasAddedToPageBuilder($snippetCode, json_encode($translations));
    }
}
