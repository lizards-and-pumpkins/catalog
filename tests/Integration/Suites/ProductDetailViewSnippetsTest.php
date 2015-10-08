<?php


namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Product\Composite\ConfigurableProduct;
use LizardsAndPumpkins\Product\SimpleProduct;
use LizardsAndPumpkins\Utils\XPathParser;

class ProductDetailViewSnippetsTest extends AbstractIntegrationTest
{
    /**
     * @var SampleMasterFactory
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
     * @param string $productIdString
     * @return string
     */
    private function getProductJsonSnippetForId($productIdString)
    {
        $key = $this->getProductJsonSnippetKeyForId($productIdString);
        $reader = $this->factory->createDataPoolReader();
        return $reader->getSnippet($key);
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
        $key = $keyGenerator->getKeyForContext($context, ['product_id' => $productIdString]);
        return $key;
    }

    public function testProductJsonSnippetIsWrittenToDataPool()
    {
        $this->importCatalog();
        
        $productIdString = $this->getSkuOfFirstSimpleProductInFixture();

        $snippet = $this->getProductJsonSnippetForId($productIdString);

        $this->assertEquals($productIdString, SimpleProduct::fromArray(json_decode($snippet, true))->getId());
    }
}
