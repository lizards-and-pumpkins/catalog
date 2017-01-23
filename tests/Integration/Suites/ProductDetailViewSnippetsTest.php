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

    private function importCatalog()
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/catalog_import');
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.catalog_import.v1+json'
        ]);
        $httpRequestBodyString = json_encode(['fileName' => 'catalog.xml']);
        $httpRequestBody = new HttpRequestBody($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);
        $implementationSpecificFactory = $this->getIntegrationTestFactory($this->factory);

        $website = new InjectableDefaultWebFront($request, $this->factory, $implementationSpecificFactory);
        $website->processRequest();

        $this->processAllMessages($this->factory);
    }

    private function getSkuOfFirstSimpleProductInFixture() : string
    {
        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/catalog.xml');
        $parser = new XPathParser($xml);
        $skuNode = $parser->getXmlNodesArrayByXPath('//catalog/products/product[@type="simple"][1]/@sku');
        return $skuNode[0]['value'];
    }

    private function getSkuOfFirstConfigurableProductInFixture() : string
    {
        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/catalog.xml');
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
        $context = $this->factory->createContext();
        return $keyGenerator->getKeyForContext($context, ['product_id' => $productIdString]);
    }

    private function getConfigurableProductVariationAttributesJsonSnippetKeyForId(string $productIdString) : string
    {
        $keyGenerator = $this->factory->createConfigurableProductVariationAttributesJsonSnippetKeyGenerator();
        $context = $this->factory->createContext();
        return $keyGenerator->getKeyForContext($context, ['product_id' => $productIdString]);
    }

    private function getConfigurableProductAssociatedProductsJsonSnippetKeyForId(string $productIdString) : string
    {
        $keyGenerator = $this->factory->createConfigurableProductAssociatedProductsJsonSnippetKeyGenerator();
        $context = $this->factory->createContext();
        return $keyGenerator->getKeyForContext($context, ['product_id' => $productIdString]);
    }

    public function testProductJsonSnippetsAreWrittenToDataPool()
    {
        $this->importCatalog();
        $this->failIfMessagesWhereLogged($this->factory->getLogger());
        
        $simpleProductIdString = $this->getSkuOfFirstSimpleProductInFixture();

        $simpleProductSnippet = $this->getProductJsonSnippetForId($simpleProductIdString);

        $simpleProductData = json_decode($simpleProductSnippet, true);
        $this->assertEquals($simpleProductIdString, $simpleProductData['product_id']);
        $this->assertEquals('simple', $simpleProductData['type_code']);


        $configProductIdString = $this->getSkuOfFirstConfigurableProductInFixture();
        $variationAttributes = $this->getConfigurableProductVariationAttributesJsonSnippetForId($configProductIdString);
        $associatedProducts = $this->getConfigurableProductAssociatedProductsJsonSnippetForId($configProductIdString);

        $this->assertInternalType('array', json_decode($variationAttributes, true));
        $this->assertInternalType('array', json_decode($associatedProducts, true));
    }

    public function testConfigurableProductJsonSnippetsAreAlsoWrittenForSimpleProducts()
    {
        $this->importCatalog();
        $this->failIfMessagesWhereLogged($this->factory->getLogger());

        $productIdString = $this->getSkuOfFirstSimpleProductInFixture();
        $variationsSnippet = $this->getConfigurableProductVariationAttributesJsonSnippetForId($productIdString);
        $associatedProductSnippet = $this->getConfigurableProductAssociatedProductsJsonSnippetForId($productIdString);
        $this->assertEmpty(json_decode($variationsSnippet, true));
        $this->assertEmpty(json_decode($associatedProductSnippet, true));
    }
}
