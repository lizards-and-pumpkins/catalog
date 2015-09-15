<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Http\HttpHeaders;
use LizardsAndPumpkins\Http\HttpRequestBody;
use LizardsAndPumpkins\Http\HttpResourceNotFoundResponse;
use LizardsAndPumpkins\Product\Product;
use LizardsAndPumpkins\Product\ProductInListingSnippetRenderer;
use LizardsAndPumpkins\Product\ProductDetailViewInContextSnippetRenderer;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\Utils\XPathParser;

class EdgeToEdgeImportCatalogTest extends AbstractIntegrationTest
{
    /**
     * @var SampleMasterFactory
     */
    private $factory;

    /**
     * @param string $importFileName
     */
    private function importCatalog($importFileName)
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/catalog_import');
        $httpHeaders = HttpHeaders::fromArray([
            'Accept' => 'application/vnd.lizards-and-pumpkins.catalog_import.v1+json'
        ]);
        $httpRequestBodyString = json_encode(['fileName' => $importFileName]);
        $httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $this->factory = $this->prepareIntegrationTestMasterFactoryForRequest($request);

        $website = new InjectableSampleWebFront($request, $this->factory);
        $website->runWithoutSendingResponse();

        $this->factory->createCommandConsumer()->process();
        $this->factory->createDomainEventConsumer()->process();
    }

    public function testCatalogImportDomainEventPutsProductToKeyValueStoreAndSearchIndex()
    {
        $productId = ProductId::fromString('118235-251');
        $productName = 'LED Arm-Signallampe';
        $productPrice = 1295;
        $productBackOrderAvailability = 'true';

        $this->importCatalog('catalog.xml');

        $logger = $this->factory->getLogger();
        $this->failIfMessagesWhereLogged($logger);

        $dataPoolReader = $this->factory->createDataPoolReader();

        $keyGeneratorLocator = $this->factory->getSnippetKeyGeneratorLocator();

        $contextSource = $this->factory->createContextSource();
        $context = $contextSource->getAllAvailableContexts()[0];

        $productDetailViewKeyGenerator = $keyGeneratorLocator->getKeyGeneratorForSnippetCode(
            ProductDetailViewInContextSnippetRenderer::CODE
        );
        $productDetailViewKey = $productDetailViewKeyGenerator->getKeyForContext(
            $context,
            [Product::ID => $productId]
        );
        $productDetailViewHtml = $dataPoolReader->getSnippet($productDetailViewKey);

        $this->assertContains(
            (string) $productId,
            $productDetailViewHtml,
            sprintf('The result page HTML does not contain the expected sku "%s"', $productId)
        );
        $this->assertContains(
            $productName,
            $productDetailViewHtml,
            sprintf('The result page HTML does not contain the expected product name "%s"', $productName)
        );

        $listingPageKeyGenerator = $keyGeneratorLocator->getKeyGeneratorForSnippetCode(
            ProductInListingSnippetRenderer::CODE
        );
        $listingPageKey = $listingPageKeyGenerator->getKeyForContext($context, [Product::ID => $productId]);
        $productListingHtml = $dataPoolReader->getSnippet($listingPageKey);

        $this->assertContains(
            $productName,
            $productListingHtml,
            sprintf('Product in listing snippet HTML does not contain the expected product name "%s"', $productName)
        );

        $priceSnippetKeyGenerator = $keyGeneratorLocator->getKeyGeneratorForSnippetCode('price');
        $priceSnippetKey = $priceSnippetKeyGenerator->getKeyForContext($context, [Product::ID => $productId]);
        $priceSnippetContents = $dataPoolReader->getSnippet($priceSnippetKey);

        $this->assertEquals($productPrice, $priceSnippetContents);

        $backOrderAvailabilitySnippetKeyGenerator = $keyGeneratorLocator->getKeyGeneratorForSnippetCode('backorders');
        $backOrderAvailabilitySnippetKey = $backOrderAvailabilitySnippetKeyGenerator->getKeyForContext(
            $context,
            [Product::ID => $productId]
        );
        $backOrderAvailabilitySnippetContents = $dataPoolReader->getSnippet($backOrderAvailabilitySnippetKey);

        $this->assertEquals($productBackOrderAvailability, $backOrderAvailabilitySnippetContents);

        $searchResults = $dataPoolReader->getSearchResults('led', $context);

        $this->assertEquals(
            $productId,
            $searchResults->getDocuments()[0]->getProductId(),
            sprintf('The search result does not contain the expected product ID "%s"', $productId)
        );
    }

    public function testImportedProductIsAccessibleFromTheFrontend()
    {
        $this->importCatalog('catalog.xml');

        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/catalog.xml');
        $urlKeys = (new XPathParser($xml))->getXmlNodesArrayByXPath(
            '//catalog/products/product/attributes/url_key[@locale="de_DE"]'
        );

        $httpUrl = HttpUrl::fromString('http://example.com/' . $urlKeys[0]['value']);
        $httpHeaders = HttpHeaders::fromArray([]);
        $httpRequestBody = HttpRequestBody::fromString('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_GET, $httpUrl, $httpHeaders, $httpRequestBody);
        $this->prepareIntegrationTestMasterFactoryForRequest($request);
        
        $website = new InjectableSampleWebFront($request, $this->factory);
        $response = $website->runWithoutSendingResponse();

        $this->assertContains('<body>', $response->getBody());
    }

    public function testHttpResourceNotFoundResponseIsReturned()
    {
        $url = HttpUrl::fromString('http://example.com/non/existent/path');
        $headers = HttpHeaders::fromArray([]);
        $requestBody = HttpRequestBody::fromString('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_GET, $url, $headers, $requestBody);

        $website = new SampleWebFront($request);
        $website->registerFactory(new IntegrationTestFactory());
        $response = $website->runWithoutSendingResponse();
        $this->assertInstanceOf(HttpResourceNotFoundResponse::class, $response);
    }
    
    public function testProductsWithValidDataAreImportedAndInvalidDataAreNotImportedButLogged()
    {
        $this->importCatalog('catalog-with-invalid-product.xml');

        $dataPoolReader = $this->factory->createDataPoolReader();

        $contextSource = $this->factory->createContextSource();
        $context = $contextSource->getAllAvailableContexts()[0];

        $keyGeneratorLocator = $this->factory->getSnippetKeyGeneratorLocator();
        $productDetailViewKeyGenerator = $keyGeneratorLocator->getKeyGeneratorForSnippetCode(
            ProductDetailViewInContextSnippetRenderer::CODE
        );

        $validProductId = ProductId::fromString('288193NEU');
        $validProductDetailViewSnippetKey = $productDetailViewKeyGenerator->getKeyForContext(
            $context,
            [Product::ID => $validProductId]
        );

        $invalidProductId = ProductId::fromString('T4H2N-4701');
        $invalidProductDetailViewSnippetKey = $productDetailViewKeyGenerator->getKeyForContext(
            $context,
            [Product::ID => $invalidProductId]
        );

        $this->assertTrue($dataPoolReader->hasSnippet($validProductDetailViewSnippetKey));
        $this->assertFalse($dataPoolReader->hasSnippet($invalidProductDetailViewSnippetKey));

        $logger = $this->factory->getLogger();
        $messages = $logger->getMessages();

        $importExceptionMessage = 'Attributes with different context parts can not be combined into one list';
        $expectedLoggedErrorMessage = sprintf(
            "Failed to import product ID: %s due to following reason:\n%s",
            $invalidProductId,
            $importExceptionMessage
        );
        $this->assertContains($expectedLoggedErrorMessage, $messages, 'Product import failure was not logged.');

        if (!empty($messages)) {
            $messageString = implode(PHP_EOL, $messages);
            if ($messageString !== $expectedLoggedErrorMessage) {
                $this->fail($messageString);
            }
        }
    }
}
