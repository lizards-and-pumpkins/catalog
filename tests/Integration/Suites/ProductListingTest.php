<?php

namespace Brera;

use Brera\Context\Context;
use Brera\Context\ContextBuilder;
use Brera\DataPool\DataPoolReader;
use Brera\DataPool\KeyValue\InMemory\InMemoryKeyValueStore;
use Brera\DataPool\KeyValue\KeyValueStore;
use Brera\DataPool\SearchEngine\InMemorySearchEngine;
use Brera\Http\HttpUrl;
use Brera\Product\ProductListingMetaInfoSnippetContent;
use Brera\Product\ProductListingRequestHandler;
use Brera\Product\ProductListingSavedDomainEvent;
use Brera\Product\ProductListingSnippetRenderer;

class ProductListingTestAbstract extends AbstractIntegrationTest
{
    private $dummyProductInListingContent = 'A Dummy Product In A Listing';
    private $testUrl = 'http://example.com/men-accessories';

    /**
     * @test
     */
    public function itShouldPutAProductListingMetaSnippetIntoDataPool()
    {
        $factory = $this->prepareIntegrationTestMasterFactory();

        /* TODO: Fetch URL key from XML */
        $urlKey = 'men-accessories';
        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/product-listing.xml');

        $queue = $factory->getEventQueue();
        $queue->add(new ProductListingSavedDomainEvent($xml));

        $consumer = $factory->createDomainEventConsumer();
        $numberOfMessages = 1;
        $consumer->process($numberOfMessages);

        $logger = $factory->getLogger();
        $this->failIfMessagesWhereLogged($logger);

        $contextSource = $factory->createContextSource();
        $context = $contextSource->getAllAvailableContexts()[1];

        $url = HttpUrl::fromString('http://example.com/' . $urlKey);
        $metaInfoSnippetKey = (new PoCUrlPathKeyGenerator())->getUrlKeyForUrlInContext($url, $context);

        $dataPoolReader = $factory->createDataPoolReader();

        $expectedMetaInfoContent = $this->getStubMetaInfo();

        $metaInfoSnippet = $dataPoolReader->getSnippet($metaInfoSnippetKey);
        $decodedMetaInfoSnippet = json_decode($metaInfoSnippet, true);

        $this->assertSame($expectedMetaInfoContent, $decodedMetaInfoSnippet);
    }

    /**
     * @test
     */
    public function itShouldReturnProductListingPageHtml()
    {
        $keyValueStore = new InMemoryKeyValueStore();
        $dataPoolReader = new DataPoolReader($keyValueStore, new InMemorySearchEngine());
        
        $contextBuilder = new ContextBuilder(DataVersion::fromVersionString('1.0'));
        $context = $contextBuilder->getContext(['website' => 'ru', 'language' => 'de_DE']);
        $pageMetaInfoSnippetKey = $this->getPageMetaInfoSnippetKey($context);
        $this->writeProductListingFixturesIntoKeyValueStore($keyValueStore, $context);

        $productListingRequestHandler = $this->getProductListingRequestHandler(
            $pageMetaInfoSnippetKey,
            $context,
            $dataPoolReader
        );
        $page = $productListingRequestHandler->process();
        $body = $page->getBody();

        $expectedContentWithProductInListingPutIntoRootSnippet =
            '<div>' . $this->dummyProductInListingContent . '</div>';

        $this->assertEquals($expectedContentWithProductInListingPutIntoRootSnippet, $body);
    }

    private function writeProductListingFixturesIntoKeyValueStore(KeyValueStore $keyValueStore, Context $context)
    {
        $productListingMetaDataSnippetKey = $this->getPageMetaInfoSnippetKey($context);
        $productInListingSnippetCode = 'product_in_listing_118235-251';
        
        $productListingMetaDataSnippetContent = json_encode([
            ProductListingMetaInfoSnippetContent::KEY_CRITERIA => [],
            PageMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE =>  'product_listing',
            PageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES => [$productInListingSnippetCode]
        ]);
        $keyValueStore->set($productListingMetaDataSnippetKey, $productListingMetaDataSnippetContent);

        $productListingRootSnippetKey = 'product_listing_' . $context->getId();
        $productListingRootSnippetContent = '<div>{{snippet product_1}}</div>';
        $keyValueStore->set($productListingRootSnippetKey, $productListingRootSnippetContent);

        $productInListingSnippetKey = $productInListingSnippetCode . '_' . $context->getId();
        $keyValueStore->set($productInListingSnippetKey, $this->dummyProductInListingContent);
    }

    /**
     * @param string $pageMetaInfoSnippetKey
     * @param Context $context
     * @param DataPoolReader $dataPoolReader
     * @return ProductListingRequestHandler
     */
    private function getProductListingRequestHandler($pageMetaInfoSnippetKey, $context, $dataPoolReader)
    {
        return new ProductListingRequestHandler(
            $pageMetaInfoSnippetKey,
            $context,
            new SnippetKeyGeneratorLocator(),
            $dataPoolReader,
            new InMemoryLogger()
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
}
