<?php

namespace Brera;

use Brera\Http\HttpHeaders;
use Brera\Http\HttpRequestBody;
use Brera\Http\HttpResourceNotFoundResponse;
use Brera\Product\ProductDetailViewInContextSnippetRenderer;
use Brera\Product\ProductId;
use Brera\Http\HttpUrl;
use Brera\Http\HttpRequest;
use Brera\Product\ProductInListingInContextSnippetRenderer;
use Brera\Utils\XPathParser;

class EdgeToEdgeTest extends AbstractIntegrationTest
{
    /**
     * @var SampleMasterFactory
     */
    private $factory;

    public function testCatalogImportDomainEventPutsProductToKeyValueStoreAndSearchIndex()
    {
        // TODO: Test is broken, the import and the following request should initialize their own WebFront instances,
        // TODO: thus sharing the data pool and queue needs to be handled properly.

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
            ['product_id' => $productId]
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
            ProductInListingInContextSnippetRenderer::CODE
        );
        $listingPageKey = $listingPageKeyGenerator->getKeyForContext($context, ['product_id' => $productId]);
        $productListingHtml = $dataPoolReader->getSnippet($listingPageKey);

        $this->assertContains(
            $productName,
            $productListingHtml,
            sprintf('Product in listing snippet HTML does not contain the expected product name "%s"', $productName)
        );

        $priceSnippetKeyGenerator = $keyGeneratorLocator->getKeyGeneratorForSnippetCode('price');
        $priceSnippetKey = $priceSnippetKeyGenerator->getKeyForContext($context, ['product_id' => $productId]);
        $priceSnippetContents = $dataPoolReader->getSnippet($priceSnippetKey);

        $this->assertEquals($productPrice, $priceSnippetContents);

        $backOrderAvailabilitySnippetKeyGenerator = $keyGeneratorLocator->getKeyGeneratorForSnippetCode('backorders');
        $backOrderAvailabilitySnippetKey = $backOrderAvailabilitySnippetKeyGenerator->getKeyForContext(
            $context,
            ['product_id' => $productId]
        );
        $backOrderAvailabilitySnippetContents = $dataPoolReader->getSnippet($backOrderAvailabilitySnippetKey);

        $this->assertEquals($productBackOrderAvailability, $backOrderAvailabilitySnippetContents);

        $searchResults = $dataPoolReader->getSearchResults('led', $context);

        $this->assertContains(
            (string) $productId,
            $searchResults,
            sprintf('The search result does not contain the expected product ID "%s"', $productId),
            false,
            false
        );
    }

    public function testImportedProductIsAccessibleFromTheFrontend()
    {
        // TODO: Test is broken, the import and the following request should initialize their own WebFront instances,
        // TODO: thus sharing the data pool and queue needs to be handled properly.

        $this->importCatalog('catalog.xml');

        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/catalog.xml');
        $urlKeys = (new XPathParser($xml))->getXmlNodesArrayByXPath(
            '//catalog/products/product/attributes/url_key[@locale="en_US"]'
        );

        $httpUrl = HttpUrl::fromString('http://example.com/' . $urlKeys[0]['value']);
        $httpHeaders = HttpHeaders::fromArray([]);
        $httpRequestBody = HttpRequestBody::fromString('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_GET, $httpUrl, $httpHeaders, $httpRequestBody);

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
        // TODO: Test is broken, the import and the following request should initialize their own WebFront instances,
        // TODO: thus sharing the data pool and queue needs to be handled properly.

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
            ['product_id' => $validProductId]
        );

        $invalidProductId = ProductId::fromString('T4H2N-4701');
        $invalidProductDetailViewSnippetKey = $productDetailViewKeyGenerator->getKeyForContext(
            $context,
            ['product_id' => $invalidProductId]
        );

        $this->assertTrue($dataPoolReader->hasSnippet($validProductDetailViewSnippetKey));
        $this->assertFalse($dataPoolReader->hasSnippet($invalidProductDetailViewSnippetKey));

        $logger = $this->factory->getLogger();
        $messages = $logger->getMessages();

        $importExceptionMessage = 'Attributes with different context parts can not be combined into a list';
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
        /**
     * @param string $importFileName
     */
    private function importCatalog($importFileName)
    {
        $httpUrl = HttpUrl::fromString('http://example.com/api/catalog_import');
        $httpHeaders = HttpHeaders::fromArray(['Accept' => 'application/vnd.brera.catalog_import.v1+json']);
        $httpRequestBodyString = json_encode(['fileName' => $importFileName]);
        $httpRequestBody = HttpRequestBody::fromString($httpRequestBodyString);
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_PUT, $httpUrl, $httpHeaders, $httpRequestBody);

        $this->factory = $this->prepareIntegrationTestMasterFactory($request);

        $website = new InjectableSampleWebFront($request, $this->factory);
        $website->runWithoutSendingResponse();

        $this->factory->createCommandConsumer()->process();
        $this->factory->createDomainEventConsumer()->process();
    }
}
