<?php

namespace Brera;

use Brera\Context\Context;
use Brera\Http\HttpUrl;
use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\ProductListingMetaInfoSnippetContent;
use Brera\Product\ProductListingRequestHandler;
use Brera\Product\ProductListingSavedDomainEvent;
use Brera\Product\ProductListingSnippetRenderer;

class ProductListingTest extends AbstractIntegrationTest
{
    private $testUrl = 'http://example.com/men-accessories';

    /**
     * @var PoCMasterFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = $this->prepareIntegrationTestMasterFactory();
    }
    
    public function testProductListingMetaSnippetIsWrittenIntoDataPool()
    {
        $this->addProductListingCriteriaDomainDomainEventFixture();
        $this->processDomainEvents(1);
        
        /* TODO: Fetch URL key from XML */
        $urlKey = 'men-accessories';

        $logger = $this->factory->getLogger();
        $this->failIfMessagesWhereLogged($logger);

        $contextSource = $this->factory->createContextSource();
        $context = $contextSource->getAllAvailableContexts()[1];

        $url = HttpUrl::fromString('http://example.com/' . $urlKey);
        $metaInfoSnippetKey =  ProductListingSnippetRenderer::CODE . '_'
            . (new PoCUrlPathKeyGenerator())->getUrlKeyForUrlInContext($url, $context);

        $dataPoolReader = $this->factory->createDataPoolReader();

        $expectedMetaInfoContent = $this->getStubMetaInfo();

        $metaInfoSnippet = $dataPoolReader->getSnippet($metaInfoSnippetKey);
        $decodedMetaInfoSnippet = json_decode($metaInfoSnippet, true);

        $this->assertSame($expectedMetaInfoContent, $decodedMetaInfoSnippet);
    }

    public function testProductListingPageHtmlIsReturned()
    {
        $this->addRootTemplateChangedDomainEventToSetupProductListingFixture();
        $this->addProductImportDomainEventToSetUpProductFixture();
        $this->addProductListingCriteriaDomainDomainEventFixture();

        $this->processDomainEvents(5);
        
        $this->factory->getSnippetKeyGeneratorLocator()->register(
            ProductListingSnippetRenderer::CODE,
            $this->factory->createProductListingSnippetKeyGenerator()
        );
        
        $productListingRequestHandler = $this->getProductListingRequestHandler();
        $page = $productListingRequestHandler->process();
        $body = $page->getBody();

        // @todo: read from XML
        $expectedProductName = 'LED Armflasher';

        $this->assertContains($expectedProductName, $body);
    }
    
    private function addRootTemplateChangedDomainEventToSetupProductListingFixture()
    {
        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/product-listing-root-snippet.xml');
        $queue = $this->factory->getEventQueue();
        $queue->add(new RootTemplateChangedDomainEvent($xml));
    }

    private function addProductImportDomainEventToSetUpProductFixture()
    {
        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/product.xml');
        $queue = $this->factory->getEventQueue();
        $queue->add(new CatalogImportDomainEvent($xml));
    }

    private function addProductListingCriteriaDomainDomainEventFixture()
    {
        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/product-listing.xml');
        $queue = $this->factory->getEventQueue();
        $queue->add(new ProductListingSavedDomainEvent($xml));
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
        return (new PoCUrlPathKeyGenerator())->getUrlKeyForUrlInContext($url, $context);
    }

    /**
     * @return mixed[]
     */
    private function getStubMetaInfo()
    {
        $metaSnippetContent = ProductListingMetaInfoSnippetContent::create(
            ['category' => 'men-accessories'],
            ProductListingSnippetRenderer::CODE,
            []
        );

        return $metaSnippetContent->getInfo();
    }

    /**
     * @param int $numberOfMessages
     */
    private function processDomainEvents($numberOfMessages)
    {
        $consumer = $this->factory->createDomainEventConsumer();
        $consumer->process($numberOfMessages);
    }
}
