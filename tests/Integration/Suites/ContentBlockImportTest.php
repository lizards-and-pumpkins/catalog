<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;
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
        $httpRequestBody = new HttpRequestBody($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $implementationSpecificFactory = $this->getIntegrationTestFactory($this->factory);

        $website = new InjectableDefaultWebFront($request, $this->factory, $implementationSpecificFactory);
        $website->processRequest();

        $this->processAllMessages($this->factory);
    }

    private function renderProductListingTemplate()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/templates/product_listing');
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.templates.v1+json'
        ]);
        $httpRequestBody = new HttpRequestBody('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $implementationSpecificFactory = $this->getIntegrationTestFactory($this->factory);

        $website = new InjectableDefaultWebFront($request, $this->factory, $implementationSpecificFactory);
        $website->processRequest();

        $this->processAllMessages($this->factory);
    }

    private function getProductListingPageHtmlByUrlKey(string $urlKey) : string
    {
        $request = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString('http://example.com/' . $urlKey),
            HttpHeaders::fromArray([]),
            new HttpRequestBody('')
        );

        $productListingRequestHandler = $this->factory->createProductListingRequestHandler();
        $page = $productListingRequestHandler->process($request);

        return $page->getBody();
    }

    private function importContentBlockViaApi(string $snippetCode, string $httpRequestBodyString)
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/content_blocks/' . $snippetCode);
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.content_blocks.v1+json'
        ]);
        $httpRequestBody = new HttpRequestBody($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $domainCommandQueue = $this->factory->getCommandMessageQueue();
        $this->assertEquals(0, $domainCommandQueue->count());

        $implementationSpecificFactory = $this->getIntegrationTestFactory($this->factory);

        $website = new InjectableDefaultWebFront($request, $this->factory, $implementationSpecificFactory);
        $response = $website->processRequest();

        $this->assertSame('', $response->getBody());
        $this->assertSame(202, $response->getStatusCode());
        $this->assertEquals(1, $domainCommandQueue->count());

        $this->processAllMessages($this->factory);

        $logger = $this->factory->getLogger();
        $this->failIfMessagesWhereLogged($logger);
    }

    /**
     * @param string $snippetCode
     * @param mixed[] $keyGeneratorParameters
     * @return string
     */
    private function getContentBlockSnippetContent(string $snippetCode, array $keyGeneratorParameters) : string
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
            new HttpRequestBody('')
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
