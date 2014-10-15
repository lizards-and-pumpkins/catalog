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

        
        // TODO refactor and create application for backend
        $factory = new PoCMasterFactory();
        $factory->register(new IntegrationTestFactory());

        $repository = $factory->getProductRepository();
        $repository->createProduct($productId, $productName);

        $queue = $factory->getEventQueue();
        $queue->add(new ProductCreatedDomainEvent($productId));

        $consumer = $factory->createDomainEventConsumer();
        $consumer->process(1);

        $reader = $factory->createDataPoolReader();
        $html = $reader->getPoCProductHtml($productId);

        $this->assertContains((string)$sku, $html);
        $this->assertContains($productName, $html);
    }

    /**
     * @test
     */
    public function pageRequestShouldDisplayAProduct()
    {
        $html = '<p>some html</p>';

        $httpUrl = HttpUrl::fromString('http://example.com/seo-url');
        $request = HttpRequest::fromParameters('GET', $httpUrl);

        $sku = new SkuStub('test');
        $productId = ProductId::fromSku($sku);

        $factory = new PoCMasterFactory();
        $factory->register(new FrontendFactory());
        $factory->register(new IntegrationTestFactory());

        $dataPoolWriter = $factory->createDataPoolWriter();
        $dataPoolWriter->setProductIdBySeoUrl($productId, $httpUrl);
        $dataPoolWriter->setPoCProductHtml($productId, $html);

        $website = new PoCWebsite($request, $factory);
        $response = $website->run(false);
        
        $this->assertContains($html, $response->getBody());
    }
}
