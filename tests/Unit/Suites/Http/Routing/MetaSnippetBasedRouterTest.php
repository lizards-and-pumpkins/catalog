<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\Routing;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\Website\UrlToWebsiteMap;
use LizardsAndPumpkins\DataPool\KeyValueStore\Exception\KeyNotFoundException;
use LizardsAndPumpkins\DataPool\SnippetReader;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Http\Routing\Exception\MalformedMetaSnippetException;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Http\Routing\MetaSnippetBasedRouter
 */
class MetaSnippetBasedRouterTest extends TestCase
{
    /**
     * @var UrlToWebsiteMap|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubUrlToWebsiteMap;

    /**
     * @var SnippetReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSnippetReader;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $dummyContext;

    /**
     * @var MetaSnippetBasedRouter
     */
    private $router;

    /**
     * @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubRequest;

    private function mockMetaJsonSnippet(string $metaJson)
    {
        $testUrl = 'http://example.com/bar/';
        $testUrlKey = 'bar';

        $this->stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->with($testUrl)
            ->willReturn($testUrlKey);

        $stubUrl = $this->createMock(HttpUrl::class);
        $stubUrl->method('__toString')->willReturn($testUrl);

        $this->stubRequest->method('getUrl')->willReturn($stubUrl);

        $this->stubSnippetReader->method('getPageMetaSnippet')->with($testUrlKey, $this->dummyContext)
            ->willReturn($metaJson);
    }

    final protected function setUp()
    {
        $this->stubUrlToWebsiteMap = $this->createMock(UrlToWebsiteMap::class);
        $this->stubSnippetReader = $this->createMock(SnippetReader::class);
        $this->dummyContext = $this->createMock(Context::class);

        $this->router = new MetaSnippetBasedRouter(
            $this->stubUrlToWebsiteMap,
            $this->stubSnippetReader,
            $this->dummyContext
        );

        $this->stubRequest = $this->createMock(HttpRequest::class);
    }

    public function testReturnsNullIfPageMetaSnippetDoesNotExist()
    {
        $this->stubSnippetReader->method('getPageMetaSnippet')->willThrowException(new KeyNotFoundException);
        $this->assertNull($this->router->route($this->stubRequest));
    }

    public function testThrowsAnExceptionIfPageMEtaSnippetDoesNotContainRequestHandlerCode()
    {
        $this->expectException(MalformedMetaSnippetException::class);

        $metaJson = '{}';
        $this->mockMetaJsonSnippet($metaJson);

        $this->router->route($this->stubRequest);
    }

    public function testReturnsNullIfRouterWithGivenCodeIsNotRegistered()
    {
        $metaJson = json_encode([PageMetaInfoSnippetContent::KEY_HANDLER_CODE => 'foo']);
        $this->mockMetaJsonSnippet($metaJson);

        $this->assertNull($this->router->route($this->stubRequest));
    }

    public function testReturnsNullIfRequestHandlerCanNotProcessRequest()
    {
        $requestHandlerCode = 'foo';

        $metaJson = json_encode([PageMetaInfoSnippetContent::KEY_HANDLER_CODE => $requestHandlerCode]);
        $this->mockMetaJsonSnippet($metaJson);

        $this->router->registerHandlerCallback($requestHandlerCode, function () {
            $stubRequestHandler = $this->createMock(HttpRequestHandler::class);
            $stubRequestHandler->method('canProcess')->willReturn(false);

            return $stubRequestHandler;
        });

        $this->assertNull($this->router->route($this->stubRequest));
    }

    public function testReturnsRegisteredRequestHandler()
    {
        $requestHandlerCode = 'foo';

        $metaJson = json_encode([PageMetaInfoSnippetContent::KEY_HANDLER_CODE => $requestHandlerCode]);
        $this->mockMetaJsonSnippet($metaJson);

        $this->router->registerHandlerCallback($requestHandlerCode, function () {
            $stubRequestHandler = $this->createMock(HttpRequestHandler::class);
            $stubRequestHandler->method('canProcess')->willReturn(true);

            return $stubRequestHandler;
        });

        $this->assertInstanceOf(HttpRequestHandler::class, $this->router->route($this->stubRequest));
    }
}
