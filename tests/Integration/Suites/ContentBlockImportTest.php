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

    public function testContentBlockSnippetIswrittenIntoDataPool()
    {
        $contentBlockContent = 'bar';

        $httpUrl = HttpUrl::fromString('http://example.com/api/content_blocks/foo');
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

        $this->processCommands(1);
        $this->processDomainEvents(1);

        $logger = $this->factory->getLogger();
        $this->failIfMessagesWhereLogged($logger);

        $contextSource = $this->factory->createContextSource();
        $context = $contextSource->getAllAvailableContexts()[1];

        $snippetKeyGenerator = $this->factory->createContentBlockSnippetKeyGenerator();
        $snippetKey = $snippetKeyGenerator->getKeyForContext($context, ['content_block' => 'foo']);

        $dataPoolReader = $this->factory->createDataPoolReader();

        $snippetContent = $dataPoolReader->getSnippet($snippetKey);
        $this->assertEquals($contentBlockContent, $snippetContent);
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
