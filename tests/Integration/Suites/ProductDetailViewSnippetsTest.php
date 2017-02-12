<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\CatalogMasterFactory;

class ProductDetailViewSnippetsTest extends AbstractIntegrationTest
{
    /**
     * @var CatalogMasterFactory|CommonFactory
     */
    private $factory;
    
    private function getConfigurableProductVariationAttributesJsonSnippetForId(string $productIdString) : string
    {
        $key = $this->getConfigurableProductVariationAttributesJsonSnippetKeyForId($productIdString);
        return $this->getSnippetFromDataPool($key);
    }

    private function getConfigurableProductAssociatedProductsJsonSnippetForId(string $productIdString) : string
    {
        $key = $this->getConfigurableProductAssociatedProductsJsonSnippetKeyForId($productIdString);
        return $this->getSnippetFromDataPool($key);
    }

    private function getSnippetFromDataPool(string $key) : string
    {
        return $this->factory->createDataPoolReader()->getSnippet($key);
    }
    
    private function getConfigurableProductVariationAttributesJsonSnippetKeyForId(string $productIdString) : string
    {
        $keyGenerator = $this->factory->createConfigurableProductVariationAttributesJsonSnippetKeyGenerator();
        $context = $this->factory->createContextBuilder()->createContext([]);
        return $keyGenerator->getKeyForContext($context, ['product_id' => $productIdString]);
    }

    private function getConfigurableProductAssociatedProductsJsonSnippetKeyForId(string $productIdString) : string
    {
        $keyGenerator = $this->factory->createConfigurableProductAssociatedProductsJsonSnippetKeyGenerator();
        $context = $this->factory->createContextBuilder()->createContext([]);
        return $keyGenerator->getKeyForContext($context, ['product_id' => $productIdString]);
    }

    public function testProductJsonSnippetsAreWrittenToDataPool()
    {
        $simpleProductFixture = 'simple_product_adilette.xml';
        $configurableProductFixture = 'configurable_product_adipure.xml';
        
        $this->factory = $this->prepareIntegrationTestMasterFactory();
        $this->importCatalogFixture($this->factory, $simpleProductFixture, $configurableProductFixture);
        $this->failIfMessagesWhereLogged($this->factory->getLogger());

        $simpleProductIdString = CatalogFixtureFileQuery::getSkuOfFirstSimpleProductInFixture($simpleProductFixture);

        $simpleProductSnippet = TestDataPoolQuery::getProductJsonSnippetForId($this->factory, $simpleProductIdString);

        $simpleProductData = json_decode($simpleProductSnippet, true);
        $this->assertEquals($simpleProductIdString, $simpleProductData['product_id']);
        $this->assertEquals('simple', $simpleProductData['type_code']);

        $configProductIdString = CatalogFixtureFileQuery::getSkuOfFirstConfigurableProductInFixture($configurableProductFixture);
        
        $variationAttributes = $this->getConfigurableProductVariationAttributesJsonSnippetForId($configProductIdString);
        $associatedProducts = $this->getConfigurableProductAssociatedProductsJsonSnippetForId($configProductIdString);

        $this->assertInternalType('array', json_decode($variationAttributes, true));
        $this->assertInternalType('array', json_decode($associatedProducts, true));
    }

    public function testConfigurableProductJsonSnippetsAreAlsoWrittenForSimpleProducts()
    {
        $fixtureCatalogFile = 'simple_product_adilette.xml';

        $this->factory = $this->prepareIntegrationTestMasterFactory();
        $this->importCatalogFixture($this->factory, $fixtureCatalogFile);
        $this->failIfMessagesWhereLogged($this->factory->getLogger());

        $productIdString = CatalogFixtureFileQuery::getSkuOfFirstSimpleProductInFixture($fixtureCatalogFile);
        $variationsSnippet = $this->getConfigurableProductVariationAttributesJsonSnippetForId($productIdString);
        $associatedProductSnippet = $this->getConfigurableProductAssociatedProductsJsonSnippetForId($productIdString);
        $this->assertEmpty(json_decode($variationsSnippet, true));
        $this->assertEmpty(json_decode($associatedProductSnippet, true));
    }
}
