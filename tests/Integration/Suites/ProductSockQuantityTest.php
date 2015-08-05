<?php

namespace Brera;

use Brera\Http\HttpHeaders;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestBody;
use Brera\Http\HttpUrl;

class ProductStockQuantityTest extends AbstractIntegrationTest
{
    public function testProductStockQuantitySnippetIsWrittenIntoDataPool()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/multiple_product_stock_quantity');
        $httpHeaders = HttpHeaders::fromArray(
            ['Accept' => 'application/vnd.brera.multiple_product_stock_quantity.v1+json']
        );
        $httpRequestBodyString = json_encode(['fileName' => 'stock.xml']);
        $httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $factory = $this->prepareIntegrationTestMasterFactory();
        
        $domainCommandQueue = $factory->getCommandQueue();
        $this->assertEquals(0, $domainCommandQueue->count());

        $website = new InjectableSampleWebFront($request, $factory);
        $response = $website->runWithoutSendingResponse();

        $this->assertEquals('"OK"', $response->getBody());
        $this->assertEquals(1, $domainCommandQueue->count());

        $factory->createCommandConsumer()->process();
        $factory->createDomainEventConsumer()->process();

        $logger = $factory->getLogger();
        $this->failIfMessagesWhereLogged($logger);

        $contextSource = $factory->createContextSource();
        $context = $contextSource->getAllAvailableContexts()[1];

        $snippetKeyGenerator = $factory->createProductStockQuantityRendererSnippetKeyGenerator();
        $snippet1Key = $snippetKeyGenerator->getKeyForContext($context, ['product_id' => 'foo']);
        $snippet2Key = $snippetKeyGenerator->getKeyForContext($context, ['product_id' => 'bar']);

        $dataPoolReader = $factory->createDataPoolReader();

        $snippet1Content = $dataPoolReader->getSnippet($snippet1Key);
        $this->assertEquals(200, $snippet1Content);

        $snippet2Content = $dataPoolReader->getSnippet($snippet2Key);
        $this->assertEquals(0, $snippet2Content);
    }
}
