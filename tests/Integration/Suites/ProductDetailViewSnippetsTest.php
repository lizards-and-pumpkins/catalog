<?php

declare(strict_types=1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Import\XPathParser;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\SampleMasterFactory;

class ProductDetailViewSnippetsTest extends AbstractIntegrationTest
{
    /**
     * @var SampleMasterFactory|CommonFactory
     */
    private $factory;
    
    private function getSkuOfFirstSimpleProductInFixture(string $fixtureFile) : string
    {
        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/' . $fixtureFile);
        $parser = new XPathParser($xml);
        $skuNode = $parser->getXmlNodesArrayByXPath('//catalog/products/product[@type="simple"][1]/@sku');
        return $skuNode[0]['value'];
    }

    private function getSkuOfFirstConfigurableProductInFixture(string $fixtureFile) : string
    {
        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/' . $fixtureFile);
        $parser = new XPathParser($xml);
        $skuNode = $parser->getXmlNodesArrayByXPath('//catalog/products/product[@type="configurable"][1]/@sku');
        return $skuNode[0]['value'];
    }

    private function getProductJsonSnippetForId(string $productIdString) : string
    {
        $key = $this->getProductJsonSnippetKeyForId($productIdString);
        return $this->getSnippetFromDataPool($key);
    }

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

    private function getProductJsonSnippetKeyForId(string $productIdString) : string
    {
        $keyGenerator = $this->factory->createProductJsonSnippetKeyGenerator();
        $context = $this->factory->createContextBuilder()->createContext([]);
        return $keyGenerator->getKeyForContext($context, ['product_id' => $productIdString]);
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
        
        $simpleProductIdString = $this->getSkuOfFirstSimpleProductInFixture($simpleProductFixture);

        $simpleProductSnippet = $this->getProductJsonSnippetForId($simpleProductIdString);

        $simpleProductData = json_decode($simpleProductSnippet, true);
        $this->assertEquals($simpleProductIdString, $simpleProductData['product_id']);
        $this->assertEquals('simple', $simpleProductData['type_code']);


        $configProductIdString = $this->getSkuOfFirstConfigurableProductInFixture($configurableProductFixture);
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

        $productIdString = $this->getSkuOfFirstSimpleProductInFixture($fixtureCatalogFile);
        $variationsSnippet = $this->getConfigurableProductVariationAttributesJsonSnippetForId($productIdString);
        $associatedProductSnippet = $this->getConfigurableProductAssociatedProductsJsonSnippetForId($productIdString);
        $this->assertEmpty(json_decode($variationsSnippet, true));
        $this->assertEmpty(json_decode($associatedProductSnippet, true));
    }
}
