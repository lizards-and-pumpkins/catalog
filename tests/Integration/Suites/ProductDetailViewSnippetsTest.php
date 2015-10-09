<?php


namespace LizardsAndPumpkins;

use LizardsAndPumpkins\DataPool\KeyValue\KeyNotFoundException;
use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Product\SimpleProduct;
use LizardsAndPumpkins\Utils\XPathParser;

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
        $httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);

        $website = new InjectableSampleWebFront($request, $this->factory);
        $website->runWithoutSendingResponse();

        $this->factory->createCommandConsumer()->process();
        $this->factory->createDomainEventConsumer()->process();
    }

    /**
     * @return string
     */
    private function getSkuOfFirstSimpleProductInFixture()
    {
        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/catalog.xml');
        $parser = new XPathParser($xml);
        $skuNode = $parser->getXmlNodesArrayByXPath('//catalog/products/product[@type="simple"][1]/@sku');
        return $skuNode[0]['value'];
    }

    /**
     * @return string
     */
    private function getSkuOfFirstConfigurableProductInFixture()
    {
        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/catalog.xml');
        $parser = new XPathParser($xml);
        $skuNode = $parser->getXmlNodesArrayByXPath('//catalog/products/product[@type="configurable"][1]/@sku');
        return $skuNode[0]['value'];
    }

    /**
     * @param string $productIdString
     * @return string
     */
    private function getProductJsonSnippetForId($productIdString)
    {
        $key = $this->getProductJsonSnippetKeyForId($productIdString);
        return $this->getSnippetFromDataPool($key);
    }

    /**
     * @param string $productIdString
     * @return string
     */
    private function getConfigurableProductVariationAttributesJsonSnippetForId($productIdString)
    {
        $key = $this->getConfigurableProductVariationAttributesJsonSnippetKeyForId($productIdString);
        return $this->getSnippetFromDataPool($key);
    }

    /**
     * @param string $productIdString
     * @return string
     */
    private function getConfigurableProductAssociatedProductsJsonSnippetForId($productIdString)
    {
        $key = $this->getConfigurableProductAssociatedProductsJsonSnippetKeyForId($productIdString);
        return $this->getSnippetFromDataPool($key);
    }

    /**
     * @param string $key
     * @return string
     */
    private function getSnippetFromDataPool($key)
    {
        return $this->factory->createDataPoolReader()->getSnippet($key);
    }

    /**
     * @param string $productIdString
     * @return string
     */
    private function getProductJsonSnippetKeyForId($productIdString)
    {
        /** @var SnippetKeyGenerator $keyGenerator */
        $keyGenerator = $this->factory->createProductJsonSnippetKeyGenerator();
        $context = $this->factory->createContext();
        return $keyGenerator->getKeyForContext($context, ['product_id' => $productIdString]);
    }

    /**
     * @param string $productIdString
     * @return string
     */
    private function getConfigurableProductVariationAttributesJsonSnippetKeyForId($productIdString)
    {
        /** @var SnippetKeyGenerator $keyGenerator */
        $keyGenerator = $this->factory->createConfigurableProductVariationAttributesJsonSnippetKeyGenerator();
        $context = $this->factory->createContext();
        return $keyGenerator->getKeyForContext($context, ['product_id' => $productIdString]);
    }

    /**
     * @param string $productIdString
     * @return string
     */
    private function getConfigurableProductAssociatedProductsJsonSnippetKeyForId($productIdString)
    {
        /** @var SnippetKeyGenerator $keyGenerator */
        $keyGenerator = $this->factory->createConfigurableProductAssociatedProductsJsonSnippetKeyGenerator();
        $context = $this->factory->createContext();
        return $keyGenerator->getKeyForContext($context, ['product_id' => $productIdString]);
    }

    public function testSimpleProductJsonSnippetIsWrittenToDataPool()
    {
        $this->importCatalog();
        $this->failIfMessagesWhereLogged($this->factory->getLogger());
        
        $productIdString = $this->getSkuOfFirstSimpleProductInFixture();

        $snippet = $this->getProductJsonSnippetForId($productIdString);

        $productData = json_decode($snippet, true);
        $this->assertEquals($productIdString, $productData['product_id']);
        $this->assertEquals('simple', $productData['type_code']);
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

    public function testConfigurableProductSnippetsAreWrittenToDataPool()
    {
        $this->importCatalog();
        $this->failIfMessagesWhereLogged($this->factory->getLogger());

        $productIdString = $this->getSkuOfFirstConfigurableProductInFixture();
        $variationAttributes = $this->getConfigurableProductVariationAttributesJsonSnippetForId($productIdString);
        $associatedProducts = $this->getConfigurableProductAssociatedProductsJsonSnippetForId($productIdString);
        
        $this->assertInternalType('array', json_decode($variationAttributes, true));
        $this->assertInternalType('array', json_decode($associatedProducts, true));
    }
}
