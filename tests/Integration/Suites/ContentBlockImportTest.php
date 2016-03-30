<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Util\Factory\SampleMasterFactory;

class ContentBlockImportTest extends AbstractIntegrationTest
{
    /**
     * @var SampleMasterFactory
     */
    private $factory;

    private function importCatalog()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/catalog_import');
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.catalog_import.v1+json'
        ]);
        $httpRequestBodyString = json_encode(['fileName' => 'catalog.xml']);
        $httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $implementationSpecificFactory = $this->getIntegrationTestFactory($this->factory);

        $website = new InjectableDefaultWebFront($request, $this->factory, $implementationSpecificFactory);
        $website->runWithoutSendingResponse();

        $this->processQueueWhileMessagesPending(
            $this->factory->getCommandQueue(),
            $this->factory->createCommandConsumer()
        );
        $this->processQueueWhileMessagesPending(
            $this->factory->getEventQueue(),
            $this->factory->createDomainEventConsumer()
        );
    }

    private function renderProductListingTemplate()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/templates/product_listing');
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.templates.v1+json'
        ]);
        $httpRequestBody = HttpRequestBody::fromString('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $implementationSpecificFactory = $this->getIntegrationTestFactory($this->factory);

        $website = new InjectableDefaultWebFront($request, $this->factory, $implementationSpecificFactory);
        $website->runWithoutSendingResponse();

        $this->factory->createCommandConsumer()->process();
        $this->factory->createDomainEventConsumer()->process();
    }

    /**
     * @param string $urlKey
     * @return string
     */
    private function getProductListingPageHtmlByUrlKey($urlKey)
    {
        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString('http://example.com/' . $urlKey),
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );

        $productListingRequestHandler = $this->factory->createProductListingRequestHandler();
        $page = $productListingRequestHandler->process($request);

        return $page->getBody();
    }

    /**
     * @param string $snippetCode
     * @param string $httpRequestBodyString
     */
    private function importContentBlockViaApi($snippetCode, $httpRequestBodyString)
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/content_blocks/' . $snippetCode);
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.content_blocks.v1+json'
        ]);
        $httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $domainCommandQueue = $this->factory->getCommandQueue();
        $this->assertEquals(0, $domainCommandQueue->count());

        $implementationSpecificFactory = $this->getIntegrationTestFactory($this->factory);

        $website = new InjectableDefaultWebFront($request, $this->factory, $implementationSpecificFactory);
        $response = $website->runWithoutSendingResponse();

        $this->assertEquals('"OK"', $response->getBody());
        $this->assertEquals(1, $domainCommandQueue->count());

        $this->factory->createCommandConsumer()->process();
        $this->factory->createDomainEventConsumer()->process();

        $logger = $this->factory->getLogger();
        $this->failIfMessagesWhereLogged($logger);
    }

    /**
     * @param string $snippetCode
     * @param mixed[] $keyGeneratorParameters
     * @return string
     */
    private function getContentBlockSnippetContent($snippetCode, array $keyGeneratorParameters)
    {
        $contextSource = $this->factory->createContextSource();
        $context = $contextSource->getAllAvailableContexts()[1];

        $snippetKeyGeneratorLocator = $this->factory->createContentBlockSnippetKeyGeneratorLocatorStrategy();
        $snippetKeyGenerator = $snippetKeyGeneratorLocator->getKeyGeneratorForSnippetCode($snippetCode);
        $snippetKey = $snippetKeyGenerator->getKeyForContext($context, $keyGeneratorParameters);

        $dataPoolReader = $this->factory->createDataPoolReader();

        $snippetContent = $dataPoolReader->getSnippet($snippetKey);

        return $snippetContent;
    }

    protected function setUp()
    {
        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString('http://example.com/'),
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );
        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);
    }

    public function testContentBlockSnippetIsWrittenIntoDataPool()
    {
        $snippetCode = 'content_block_foo';
        $contentBlockContent = 'bar';

        $httpRequestBodyString = json_encode([
            'content' => $contentBlockContent,
            'context' => ['website' => 'ru', 'locale' => 'en_US'],
        ]);

        $this->importContentBlockViaApi($snippetCode, $httpRequestBodyString);

        $keyGeneratorParameters = [];
        $snippetContent = $this->getContentBlockSnippetContent($snippetCode, $keyGeneratorParameters);

        $this->assertEquals($contentBlockContent, $snippetContent);
    }

    public function testProductListingSpecificContentBlockIsWrittenIntoDataPool()
    {
        $productListingUrlKey = 'foo';
        $contentBlockContent = 'bar';
        $snippetCode = 'product_listing_content_block_baz';

        $httpRequestBodyString = json_encode([
            'content' => $contentBlockContent,
            'context' => ['website' => 'ru', 'locale' => 'en_US'],
            'url_key' => $productListingUrlKey
        ]);

        $this->importContentBlockViaApi($snippetCode, $httpRequestBodyString);

        $keyGeneratorParameters = ['url_key' => $productListingUrlKey];
        $snippetContent = $this->getContentBlockSnippetContent($snippetCode, $keyGeneratorParameters);

        $this->assertEquals($contentBlockContent, $snippetContent);
    }

    public function testProductListingSpecificContentBlockIsPresentOnProductListingPage()
    {
        $productListingUrlKey = 'sale';
        $contentBlockContent = '<p>foo</p>';
        $snippetCode = 'product_listing_content_block_top';

        $httpRequestBodyString = json_encode([
            'content' => $contentBlockContent,
            'context' => ['version' => -1, 'website' => 'fr', 'locale' => 'fr_FR'],
            'url_key' => $productListingUrlKey
        ]);

        $this->importContentBlockViaApi($snippetCode, $httpRequestBodyString);
        $this->renderProductListingTemplate();
        $this->importCatalog();
        
        $this->assertContains($contentBlockContent, $this->getProductListingPageHtmlByUrlKey('sale'));
        $this->assertNotContains($contentBlockContent, $this->getProductListingPageHtmlByUrlKey('asics'));
    }
}
