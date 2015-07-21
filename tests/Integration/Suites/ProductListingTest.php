<?php

namespace Brera;

use Brera\Context\Context;
use Brera\DataPool\SearchEngine\SearchCriteria;
use Brera\DataPool\SearchEngine\SearchCriterion;
use Brera\Http\HttpHeaders;
use Brera\Http\HttpRequest;
use Brera\Http\HttpRequestBody;
use Brera\Http\HttpUrl;
use Brera\Product\ProductListingMetaInfoSnippetContent;
use Brera\Product\ProductListingRequestHandler;
use Brera\Product\ProductListingSnippetRenderer;
use Brera\Utils\XPathParser;

class ProductListingTest extends AbstractIntegrationTest
{
    private $testUrl = 'http://example.com/adidas-men-accessories';

    /**
     * @var SampleMasterFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = $this->prepareIntegrationTestMasterFactory();
    }
    
    public function testProductListingMetaSnippetIsWrittenIntoDataPool()
    {
        $this->importCatalog();

        $this->processCommandsInQueue();
        $this->processDomainEvents();

        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/catalog.xml');
        $urlKeyNode = (new XPathParser($xml))->getXmlNodesArrayByXPath('//catalog/listings/listing[1]/@url_key');
        $urlKey = $urlKeyNode[0]['value'];

        $logger = $this->factory->getLogger();
        $this->failIfMessagesWhereLogged($logger);

        $contextSource = $this->factory->createContextSource();
        $context = $contextSource->getAllAvailableContexts()[1];

        $url = HttpUrl::fromString('http://example.com/' . $urlKey);
        $metaInfoSnippetKey =  ProductListingSnippetRenderer::CODE . '_'
            . (new SampleUrlPathKeyGenerator())->getUrlKeyForUrlInContext($url, $context);

        $dataPoolReader = $this->factory->createDataPoolReader();
        $metaInfoSnippet = $dataPoolReader->getSnippet($metaInfoSnippetKey);

        $expectedMetaInfoContent = json_encode($this->getStubMetaInfo());

        $this->assertSame($expectedMetaInfoContent, $metaInfoSnippet);
    }

    public function testProductListingPageHtmlIsReturned()
    {
        $this->addPageTemplateWasUpdatedDomainEventToSetupProductListingFixture();
        $this->importCatalog();

        $this->processCommandsInQueue();
        $this->processDomainEvents();
        
        $this->factory->getSnippetKeyGeneratorLocator()->register(
            ProductListingSnippetRenderer::CODE,
            $this->factory->createProductListingSnippetKeyGenerator()
        );

        $httpRequest = HttpRequest::fromParameters(
            HttpRequest::METHOD_GET,
            HttpUrl::fromString('http://www.example.com'),
            HttpHeaders::fromArray([]),
            HttpRequestBody::fromString('')
        );

        $productListingRequestHandler = $this->getProductListingRequestHandler();
        $page = $productListingRequestHandler->process($httpRequest);
        $body = $page->getBody();

        /* TODO: read from XML */
        $expectedProductName = 'Adilette';
        $unExpectedProductName = 'LED Armflasher';

        $this->assertContains($expectedProductName, $body);
        $this->assertNotContains($unExpectedProductName, $body);
    }
    
    private function addPageTemplateWasUpdatedDomainEventToSetupProductListingFixture()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/v1/page_templates/product_listing');
        $httpHeaders = HttpHeaders::fromArray([]);
        $httpRequestBodyString = file_get_contents(__DIR__ . '/../../shared-fixture/product-listing-root-snippet.xml');
        $httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $website = new SampleWebFront($request, $this->factory);
        $website->runWithoutSendingResponse();
    }

    private function importCatalog()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/v1/catalog_import');
        $httpHeaders = HttpHeaders::fromArray([]);
        $httpRequestBodyString = json_encode(['fileName' => 'catalog.xml']);
        $httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $website = new SampleWebFront($request, $this->factory);
        $website->runWithoutSendingResponse();
    }

    /**
     * @return ProductListingRequestHandler
     */
    private function getProductListingRequestHandler()
    {
        $contextBuilder = $this->factory->createContextBuilder();
        $context = $contextBuilder->getContext(['website' => 'ru', 'language' => 'en_US']);
        $dataPoolReader = $this->factory->createDataPoolReader();
        $pageBuilder = new PageBuilder(
            $dataPoolReader,
            $this->factory->getSnippetKeyGeneratorLocator(),
            $this->factory->getLogger()
        );

        return new ProductListingRequestHandler(
            $this->getPageMetaInfoSnippetKey($context),
            $context,
            $dataPoolReader,
            $pageBuilder,
            $this->factory->getSnippetKeyGeneratorLocator()
        );
    }

    /**
     * @param Context $context
     * @return string
     */
    private function getPageMetaInfoSnippetKey(Context $context)
    {
        $url = HttpUrl::fromString($this->testUrl);
        return (new SampleUrlPathKeyGenerator())->getUrlKeyForUrlInContext($url, $context);
    }

    /**
     * @return mixed[]
     */
    private function getStubMetaInfo()
    {
        $searchCriterion1 = SearchCriterion::create('category', 'men-accessories', '=');
        $searchCriterion2 = SearchCriterion::create('brand', 'Adidas', '=');
        $searchCriteria = SearchCriteria::createAnd();
        $searchCriteria->add($searchCriterion1);
        $searchCriteria->add($searchCriterion2);

        $metaSnippetContent = ProductListingMetaInfoSnippetContent::create(
            $searchCriteria,
            ProductListingSnippetRenderer::CODE,
            []
        );

        return $metaSnippetContent->getInfo();
    }

    private function processCommandsInQueue()
    {
        $queue = $this->factory->getCommandQueue();
        $consumer = $this->factory->createCommandConsumer();

        while ($queue->count() > 0) {
            $consumer->process(1);
        }
    }

    private function processDomainEvents()
    {
        $queue = $this->factory->getEventQueue();
        $consumer = $this->factory->createDomainEventConsumer();

        while ($queue->count() > 0) {
            $consumer->process(1);
        }
    }
}
