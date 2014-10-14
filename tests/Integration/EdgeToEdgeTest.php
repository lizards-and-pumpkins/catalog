<?php

namespace Brera\PoC;

/**
 * Class EdgeToEdgeTest
 * @package Brera\PoC
 */
class EdgeToEdgeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function createProductDomainEventShouldRenderAProduct()
    {
        $sku = new SkuStub('test');
        $productId = ProductId::fromSku($sku);
        $productName = 'test product name';
        $factory = new IntegrationTestFactory();

        $repository = $factory->getProductRepository();
        $repository->createProduct($productId, $productName);
        
        $queue = $factory->getEventQueue();
        $queue->add(new ProductCreatedDomainEvent($productId));
        
        $consumer = $factory->createDomainEventConsumer();
        $consumer->process(1);
        
        $reader = $factory->createDataPoolReader();
        $html = $reader->getPoCProductHtml($productId);
        
        $this->assertContains((string) $sku, $html);
        $this->assertContains($productName, $html);
    }

    /**
     * @test
     */
    public function pageRequestShouldDisplayAProduct()
    {
        $httpUrl = Url::fromString('http://example.com/seo-url');
        $request = HttpRequest::fromParameters('GET', $httpUrl);
        
        $router = new HttpRouterChain();
        $router->register(new ProductSeoUrlRouter());
        $requestHandler = $router->route($request);
        $response = $requestHandler->process($request);
        
    }
}
