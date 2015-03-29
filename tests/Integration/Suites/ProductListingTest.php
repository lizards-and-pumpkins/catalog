<?php

namespace Brera;

use Brera\Context\VersionedContext;
use Brera\DataPool\DataPoolReader;
use Brera\DataPool\KeyValue\InMemory\InMemoryKeyValueStore;
use Brera\DataPool\KeyValue\KeyValueStore;
use Brera\DataPool\SearchEngine\InMemorySearchEngine;
use Brera\Http\HttpUrl;
use Brera\Product\ProductListingRequestHandler;

class ProductListingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldReturnProductListingPageHtml()
    {
        $keyValueStore = new InMemoryKeyValueStore();
        $searchEngine = new InMemorySearchEngine();

        $dataPoolReader = new DataPoolReader($keyValueStore, $searchEngine);

        $this->writeProductListingFixturesIntoKeyValueStore($keyValueStore);

        $url = HttpUrl::fromString('http://example.com/men-accessories');
        $context = new VersionedContext(DataVersion::fromVersionString('1.0'));
        $snippetKeyGeneratorLocator = new SnippetKeyGeneratorLocator();
        $urlPathKeyGenerator = new PoCUrlPathKeyGenerator();
        $logger = new InMemoryLogger();

        $pageBuilder = new ProductListingRequestHandler(
            $urlPathKeyGenerator->getUrlKeyForUrlInContext($url, $context),
            $context,
            $snippetKeyGeneratorLocator,
            $dataPoolReader,
            $logger
        );
        $page = $pageBuilder->process();
        $body = $page->getBody();

        $expectedContentWithProductInListingPutIntoRootSnippet = '<div>bar</div>';

        $this->assertEquals($expectedContentWithProductInListingPutIntoRootSnippet, $body);
    }

    private function writeProductListingFixturesIntoKeyValueStore(KeyValueStore $keyValueStore)
    {
        $productListingMetaDataSnippetKey = '_men-accessories_v:1_0';
        $productListingMetaDataSnippetContent = '{
            "product_selection_criteria": [],
            "root_snippet_code": "product_listing",
            "page_snippet_codes": ["product_in_listing_118235-251_language:de_DE_website:ru"]
        }';
        $keyValueStore->set($productListingMetaDataSnippetKey, $productListingMetaDataSnippetContent);

        $productListingRootSnippetKey = 'product_listing__v:1.0';
        $productListingRootSnippetContent = '<div>{{snippet product_1}}</div>';
        $keyValueStore->set($productListingRootSnippetKey, $productListingRootSnippetContent);

        $productInListingSnippetKey = 'product_in_listing_118235-251_language:de_DE_website:ru__v:1.0';
        $productInListingSnippetContent = 'bar';
        $keyValueStore->set($productInListingSnippetKey, $productInListingSnippetContent);
    }
}
