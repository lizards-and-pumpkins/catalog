<?php

namespace Brera;

use Brera\Http\HttpHeaders;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestBody;
use Brera\Http\HttpUrl;

class ContentBlockImportTest extends AbstractIntegrationTest
{
    /**
     * @var SampleMasterFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = $this->prepareIntegrationTestMasterFactory();
    }

    public function testContentBlockSnippetIsWrittenIntoDataPool()
    {
        $contentBlockContent = 'bar';

        $httpUrl = HttpUrl::fromString('http://example.com/api/v1/content_blocks/foo');
        $httpHeaders = HttpHeaders::fromArray([]);
        $httpRequestBodyString = json_encode([
            'content' => $contentBlockContent,
            'context' => ['website' => 'ru', 'language' => 'en_US']
        ]);
        $httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $domainCommandQueue = $this->factory->getCommandQueue();
        $this->assertEquals(0, $domainCommandQueue->count());

        $website = new SampleWebFront($request, $this->factory);
        $response = $website->runWithoutSendingResponse();

        $this->assertEquals('"OK"', $response->getBody());
        $this->assertEquals(1, $domainCommandQueue->count());

        $this->factory->createCommandConsumer()->process();
        $this->factory->createDomainEventConsumer()->process();

        $logger = $this->factory->getLogger();
        $this->failIfMessagesWhereLogged($logger);

        $contextSource = $this->factory->createContextSource();
        $context = $contextSource->getAllAvailableContexts()[1];

        $snippetKeyGenerator = $this->factory->createContentBlockSnippetKeyGenerator();
        $snippetKey = $snippetKeyGenerator->getKeyForContext($context, ['content_block_id' => 'foo']);

        $dataPoolReader = $this->factory->createDataPoolReader();

        $snippetContent = $dataPoolReader->getSnippet($snippetKey);
        $this->assertEquals($contentBlockContent, $snippetContent);
    }
}
