<?php

namespace Brera;

use Brera\Http\HttpHeaders;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestBody;
use Brera\Http\HttpUrl;

class ProductSockQuantityTest extends AbstractIntegrationTest
{
    /**
     * @var SampleMasterFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = $this->prepareIntegrationTestMasterFactory();
    }

    public function testProductStockQuantitySnippetIsWrittenIntoDataPool()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/multiple_product_stock_quantity');
        $httpHeaders = HttpHeaders::fromArray([]);
        $httpRequestBodyString = json_encode(['fileName' => 'stock.xml']);
        $httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $domainCommandQueue = $this->factory->getCommandQueue();
        $this->assertEquals(0, $domainCommandQueue->count());

        $website = new SampleWebFront($request, $this->factory);
        $response = $website->runWithoutSendingResponse();

        $this->assertEquals('"OK"', $response->getBody());
        $this->assertEquals(1, $domainCommandQueue->count());

        $this->processCommands(3);
        $this->processDomainEvents(2);

        $logger = $this->factory->getLogger();
        $this->failIfMessagesWhereLogged($logger);

        $contextSource = $this->factory->createContextSource();
        $context = $contextSource->getAllAvailableContexts()[1];

        $snippetKeyGenerator = $this->factory->createProductStockQuantityRendererSnippetKeyGenerator();
        $snippet1Key = $snippetKeyGenerator->getKeyForContext($context, ['product_id' => 'foo']);
        $snippet2Key = $snippetKeyGenerator->getKeyForContext($context, ['product_id' => 'bar']);

        $dataPoolReader = $this->factory->createDataPoolReader();

        $snippet1Content = $dataPoolReader->getSnippet($snippet1Key);
        $this->assertEquals(200, $snippet1Content);

        $snippet2Content = $dataPoolReader->getSnippet($snippet2Key);
        $this->assertEquals(0, $snippet2Content);
    }

    /**
     * @param int $numberOfMessages
     */
    private function processDomainEvents($numberOfMessages)
    {
        $consumer = $this->factory->createDomainEventConsumer();
        $consumer->process($numberOfMessages);
    }

    /**
     * @param int $numberOfMessages
     */
    private function processCommands($numberOfMessages)
    {
        $consumer = $this->factory->createCommandConsumer();
        $consumer->process($numberOfMessages);
    }
}
