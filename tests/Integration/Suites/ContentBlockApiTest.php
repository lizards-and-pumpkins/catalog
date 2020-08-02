<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\ContentBlock\ContentDelivery\ContentBlockServiceFactory;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Util\Factory\CatalogMasterFactory;

class ContentBlockApiTest extends AbstractIntegrationTest
{
    /**
     * @var CatalogMasterFactory
     */
    private $factory;

    private function processRequest(HttpRequest $request): HttpResponse
    {
        $implementationSpecificFactory = $this->getIntegrationTestFactory($this->factory);
        $website = new InjectableRestApiWebFront($request, $this->factory, $implementationSpecificFactory);

        return $website->processRequest();
    }

    final protected function setUp(): void
    {
        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString('http://example.com/'),
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );

        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);
        $this->factory->register(new ContentBlockServiceFactory());
    }

    public function testContentBlockCanBePutAndGetViaApi(): void
    {
        $snippetCode = 'content_block_foo';
        $contentBlockContent = 'bar';

        $httpUrl = HttpUrl::fromString(sprintf('http://example.com/api/content_blocks/%s', $snippetCode));
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.content_blocks.v2+json',
        ]);

        $httpRequestBody = new HttpRequestBody(json_encode([
            'content' => $contentBlockContent,
            'context' => ['website' => 'fr', 'locale' => 'fr_FR'],
            'data_version' => '-1',
        ]));

        $getRequest = HttpRequest::fromParameters(HttpRequest::METHOD_GET, $httpUrl, $httpHeaders, $httpRequestBody);
        $getResponse = $this->processRequest($getRequest);

        $this->assertSame(HttpResponse::STATUS_NOT_FOUND, $getResponse->getStatusCode());

        $putRequest = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $domainCommandQueue = $this->factory->getCommandMessageQueue();
        $this->assertEquals(0, $domainCommandQueue->count());

        $putResponse = $this->processRequest($putRequest);

        $this->assertSame('', $putResponse->getBody());
        $this->assertSame(HttpResponse::STATUS_ACCEPTED, $putResponse->getStatusCode());
        $this->assertEquals(1, $domainCommandQueue->count());

        $this->processAllMessages($this->factory);

        $logger = $this->factory->getLogger();
        $this->failIfMessagesWhereLogged($logger);

        $httpRequestBody = new HttpRequestBody('');
        $getRequest = HttpRequest::fromParameters(HttpRequest::METHOD_GET, $httpUrl, $httpHeaders, $httpRequestBody);

        $getResponse = $this->processRequest($getRequest);

        $this->assertSame(HttpResponse::STATUS_OK, $getResponse->getStatusCode());
        $this->assertSame($contentBlockContent, $getResponse->getBody());
    }
}
