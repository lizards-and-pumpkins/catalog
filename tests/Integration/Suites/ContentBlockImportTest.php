<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Util\Factory\CatalogMasterFactory;
use LizardsAndPumpkins\Import\SnippetCode;

class ContentBlockImportTest extends AbstractIntegrationTest
{
    /**
     * @var CatalogMasterFactory
     */
    private $factory;

    private function renderProductListingTemplate()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/templates/product_listing');
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.templates.v1+json',
        ]);
        $httpRequestBody = new HttpRequestBody('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $implementationSpecificFactory = $this->getIntegrationTestFactory($this->factory);

        $website = new InjectableDefaultWebFront($request, $this->factory, $implementationSpecificFactory);
        $website->processRequest();

        $this->processAllMessages($this->factory);
    }

    private function getProductListingPageHtmlByUrlKey(string $urlKey): string
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

    private function importContentBlockViaApiV1(SnippetCode $snippetCode, string $httpRequestBodyString)
    {
        $this->importContentBlockViaApi($snippetCode, $httpRequestBodyString, 'v1');
    }

    private function importContentBlockViaApiV2(SnippetCode $snippetCode, string $httpRequestBodyString)
    {
        $this->importContentBlockViaApi($snippetCode, $httpRequestBodyString, 'v2');
    }

    private function importContentBlockViaApi(SnippetCode $snippetCode, string $httpRequestBodyString, string $version)
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/content_blocks/' . $snippetCode);
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.content_blocks.' . $version . '+json',
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
     * @param SnippetCode $snippetCode
     * @param mixed[] $keyGeneratorParameters
     * @return string
     */
    private function getContentBlockSnippetCodeForCurrentDataVersion(
        SnippetCode $snippetCode,
        array $keyGeneratorParameters
    ): string {
        $dataPoolReader = $this->factory->createDataPoolReader();
        $currentDataVersion = DataVersion::fromVersionString($dataPoolReader->getCurrentDataVersion());

        return $this->getContentBlockSnippetContent($snippetCode, $keyGeneratorParameters, $currentDataVersion);
    }

    /**
     * @param SnippetCode $snippetCode
     * @param mixed[] $keyGeneratorParameters
     * @param DataVersion $version
     * @return string
     */
    private function getContentBlockSnippetContent(
        SnippetCode $snippetCode,
        array $keyGeneratorParameters,
        DataVersion $version
    ): string {
            
        $snippetKey = $this->getContentBlockSnippetKey($snippetCode, $keyGeneratorParameters, $version);

        $dataPoolReader = $this->factory->createDataPoolReader();

        return $dataPoolReader->getSnippet($snippetKey);
    }

    private function getContentBlockSnippetKey(
        SnippetCode $snippetCode,
        array $keyGeneratorParameters,
        DataVersion $version
    ): string {
        $contextSource = $this->factory->createContextSource();
        $context = $contextSource->getAllAvailableContextsWithVersionApplied($version)[1];

        $snippetKeyGeneratorLocator = $this->factory->createContentBlockSnippetKeyGeneratorLocatorStrategy();
        $snippetKeyGenerator = $snippetKeyGeneratorLocator->getKeyGeneratorForSnippetCode($snippetCode);

        return $snippetKeyGenerator->getKeyForContext($context, $keyGeneratorParameters);
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
        $snippetCode = new SnippetCode('content_block_foo');
        $contentBlockContent = 'bar';

        $httpRequestBodyString = json_encode([
            'content' => $contentBlockContent,
            'context' => ['website' => 'ru', 'locale' => 'en_US'],
        ]);

        $this->importContentBlockViaApiV1($snippetCode, $httpRequestBodyString);

        $keyGeneratorParameters = [];
        $snippetContent = $this->getContentBlockSnippetCodeForCurrentDataVersion($snippetCode, $keyGeneratorParameters);

        $this->assertEquals($contentBlockContent, $snippetContent);
    }

    public function testProductListingSpecificContentBlockIsWrittenIntoDataPool()
    {
        $productListingUrlKey = 'foo';
        $contentBlockContent = 'bar';
        $snippetCode = new SnippetCode('product_listing_content_block_baz');

        $httpRequestBodyString = json_encode([
            'content' => $contentBlockContent,
            'context' => ['website' => 'ru', 'locale' => 'en_US'],
            'url_key' => $productListingUrlKey,
        ]);

        $this->importContentBlockViaApiV1($snippetCode, $httpRequestBodyString);

        $keyGeneratorParameters = ['url_key' => $productListingUrlKey];
        $snippetContent = $this->getContentBlockSnippetCodeForCurrentDataVersion($snippetCode, $keyGeneratorParameters);

        $this->assertEquals($contentBlockContent, $snippetContent);
    }

    public function testProductListingSpecificContentBlockIsPresentOnProductListingPage()
    {
        $productListingUrlKey = 'sale';
        $contentBlockContent = '<p>foo</p>';
        $snippetCode = new SnippetCode('product_listing_content_block_top');

        $httpRequestBodyString = json_encode([
            'content' => $contentBlockContent,
            'context' => ['website' => 'fr', 'locale' => 'fr_FR'],
            'url_key' => $productListingUrlKey,
        ]);

        $this->importContentBlockViaApiV1($snippetCode, $httpRequestBodyString);
        $this->renderProductListingTemplate();
        $this->importCatalogFixture($this->factory, 'product_listings.xml');

        $this->assertContains($contentBlockContent, $this->getProductListingPageHtmlByUrlKey('sale'));
        $this->assertNotContains($contentBlockContent, $this->getProductListingPageHtmlByUrlKey('asics'));
    }

    public function testContentBlockSnippetIsWrittenIntoDataPoolWithTheSpecifiedDataVersion()
    {
        $currentDataVersion = DataVersion::fromVersionString('foo');
        $targetDataVersion = DataVersion::fromVersionString('baz');

        $factory = $this->prepareIntegrationTestMasterFactory();
        $factory->createDataPoolWriter()->setCurrentDataVersion((string) $currentDataVersion);

        $snippetCode = new SnippetCode('content_block_foo');
        
        $httpRequestBodyString = json_encode([
            'content'      => 'bar',
            'data_version' => (string) $targetDataVersion,
            'context'      => ['website' => 'ru', 'locale' => 'en_US'],
        ]);

        $this->importContentBlockViaApiV2($snippetCode, $httpRequestBodyString);
        
        $keyGeneratorParameters = [];
        
        $keyForCurrentDataVersion = $this->getContentBlockSnippetKey(
            $snippetCode,
            $keyGeneratorParameters,
            $currentDataVersion
        );
        $keyForTargetDataVersion = $this->getContentBlockSnippetKey(
            $snippetCode,
            $keyGeneratorParameters,
            $targetDataVersion
        );
        $this->assertFalse($this->factory->createDataPoolReader()->hasSnippet($keyForCurrentDataVersion));
        $this->assertTrue($this->factory->createDataPoolReader()->hasSnippet($keyForTargetDataVersion));
    }
}
