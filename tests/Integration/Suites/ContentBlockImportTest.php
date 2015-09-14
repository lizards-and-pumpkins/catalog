<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;

class ContentBlockImportTest extends AbstractIntegrationTest
{
    public function testContentBlockSnippetIsWrittenIntoDataPool()
    {
        $contentBlockContent = 'bar';

        $httpUrl = HttpUrl::fromString('http://example.com/api/content_blocks/foo');
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.content_blocks.v1+json'
        ]);
        $httpRequestBodyString = json_encode([
            'content' => $contentBlockContent,
            'context' => ['website' => 'ru', 'locale' => 'en_US']
        ]);
        $httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);
        
        $factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);

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

        $snippetKeyGenerator = $factory->createContentBlockSnippetKeyGenerator();
        $snippetKey = $snippetKeyGenerator->getKeyForContext($context, ['content_block_id' => 'foo']);

        $dataPoolReader = $factory->createDataPoolReader();

        $snippetContent = $dataPoolReader->getSnippet($snippetKey);
        $this->assertEquals($contentBlockContent, $snippetContent);
    }
}
