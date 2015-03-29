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

class ProductListingTest extends \PHPUnit_Framework_TestCase
{
    private $dummyProductInListingContent = 'A Dummy Product In A Listing';
    private $testUrl = 'http://example.com/men-accessories';

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
}
