<?php

namespace Brera;

use Brera\Http\HttpHeaders;
use Brera\Http\HttpRequestBody;
use Brera\Http\HttpResourceNotFoundResponse;
use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\SampleSku;
use Brera\Product\ProductDetailViewInContextSnippetRenderer;
use Brera\Product\ProductId;
use Brera\Http\HttpUrl;
use Brera\Http\HttpRequest;
use Brera\Product\ProductInListingInContextSnippetRenderer;
use Brera\Product\ProductListingSnippetRenderer;
use Brera\Utils\XPathParser;

class EdgeToEdgeTest extends AbstractIntegrationTest
{
    public function testProductDomainEventPutsProductToKeyValueStoreAndSearchIndex()
    {
        $factory = $this->prepareIntegrationTestMasterFactory();

        $sku = SampleSku::fromString('118235-251');
        $productId = ProductId::fromSku($sku);
        $productName = 'LED Arm-Signallampe';
        $productPrice = 1295;
        $productBackOrderAvailability = 'true';

        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/catalog.xml');

        $queue = $factory->getEventQueue();
        $queue->add(new CatalogImportDomainEvent($xml));

        $consumer = $factory->createDomainEventConsumer();
        $numberOfMessages = 3;
        $consumer->process($numberOfMessages);
        
        $logger = $factory->getLogger();
        $this->failIfMessagesWhereLogged($logger);

        $dataPoolReader = $factory->createDataPoolReader();

        $keyGeneratorLocator = $factory->getSnippetKeyGeneratorLocator();

        $contextSource = $factory->createContextSource();
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
            (string) $sku,
            $productDetailViewHtml,
            sprintf('The result page HTML does not contain the expected sku "%s"', $sku)
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

    public function testPageTemplateWasUpdatedDomainEventPutsProductListingRootSnippetIntoKeyValueStore()
    {
        $factory = $this->prepareIntegrationTestMasterFactory();

        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/product-listing-root-snippet.xml');

        $queue = $factory->getEventQueue();
        $queue->add(new PageTemplateWasUpdatedDomainEvent($xml));

        $consumer = $factory->createDomainEventConsumer();
        $numberOfMessages = 1;
        $consumer->process($numberOfMessages);

        $logger = $factory->getLogger();
        $this->failIfMessagesWhereLogged($logger);

        $dataPoolReader = $factory->createDataPoolReader();

        $keyGeneratorLocator = $factory->getSnippetKeyGeneratorLocator();
        $keyGenerator = $keyGeneratorLocator->getKeyGeneratorForSnippetCode(
            ProductListingSnippetRenderer::CODE
        );

        $contextSource = $factory->createContextSource();
        $context = $contextSource->getAllAvailableContexts()[0];

        $key = $keyGenerator->getKeyForContext($context);
        $html = $dataPoolReader->getSnippet($key);

        $expectation = file_get_contents(__DIR__ . '/../../../theme/template/list.phtml');

        $this->assertContains($expectation, $html);
    }

    public function testImportedProductIsAccessibleFromTheFrontend()
    {
        $factory = $this->prepareIntegrationTestMasterFactory();

        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/catalog.xml');

        $queue = $factory->getEventQueue();
        $queue->add(new CatalogImportDomainEvent($xml));

        $consumer = $factory->createDomainEventConsumer();
        $numberOfMessages = 3;
        $consumer->process($numberOfMessages);
        
        $urlKeys = (new XPathParser($xml))->getXmlNodesArrayByXPath(
            '//catalog/products/product/attributes/url_key[@language="en_US"]'
        );
        
        $httpUrl = HttpUrl::fromString('http://example.com/' . $urlKeys[0]['value']);
        $httpHeaders = HttpHeaders::fromArray([]);
        $httpRequestBody = HttpRequestBody::fromString('');
        $request = HttpRequest::fromParameters(HttpRequest::METHOD_GET, $httpUrl, $httpHeaders, $httpRequestBody);

        $website = new SampleWebFront($request, $factory);
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
}
