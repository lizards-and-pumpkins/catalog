<?php

namespace Brera;

use Brera\Context\Context;
use Brera\Context\ContextBuilder;
use Brera\Context\VersionedContext;
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

    /**
     * @test
     */
    public function itShouldReturnProductListingPageHtml()
    {
        $keyValueStore = new InMemoryKeyValueStore();
        $dataPoolReader = new DataPoolReader($keyValueStore, new InMemorySearchEngine());

        $contextBuilder = new ContextBuilder(DataVersion::fromVersionString('1.0'));
        $context = $contextBuilder->getContext(['website' => 'ru', 'language' => 'de_DE']);
        $this->writeProductListingFixturesIntoKeyValueStore($keyValueStore, $context);

        $url = HttpUrl::fromString('http://example.com/men-accessories');
        $productListingRequestHandler = $this->getProductListingRequestHandler(
            (new PoCUrlPathKeyGenerator())->getUrlKeyForUrlInContext($url, $context),
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
        $productListingMetaDataSnippetKey = '_men-accessories_' . $context->getId();
        $productInListingKeyPrefix = 'product_in_listing_118235-251';
        
        $productListingMetaDataSnippetContent = json_encode([
            ProductListingMetaInfoSnippetContent::KEY_CRITERIA => [],
            PageMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE =>  'product_listing',
            PageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES => [
                $productInListingKeyPrefix . '_language:de_DE_website:ru'
            ]
        ]);
        $keyValueStore->set($productListingMetaDataSnippetKey, $productListingMetaDataSnippetContent);

        $productListingRootSnippetKey = 'product_listing_' . $context->getId();
        $productListingRootSnippetContent = '<div>{{snippet product_1}}</div>';
        $keyValueStore->set($productListingRootSnippetKey, $productListingRootSnippetContent);

        $productInListingSnippetKey = $productInListingKeyPrefix . '_language:de_DE_website:ru_' . $context->getId();
        $keyValueStore->set($productInListingSnippetKey, $this->dummyProductInListingContent);
    }

    /**
     * @param string $pageMetaInfoSnippetKey
     * @param Context $context
     * @param DataPoolReader $dataPoolReader
     * @return ProductListingRequestHandler
     */
    protected function getProductListingRequestHandler($pageMetaInfoSnippetKey, $context, $dataPoolReader)
    {
        return new ProductListingRequestHandler(
            $pageMetaInfoSnippetKey,
            $context,
            new SnippetKeyGeneratorLocator(),
            $dataPoolReader,
            new InMemoryLogger()
        );
    }
}
