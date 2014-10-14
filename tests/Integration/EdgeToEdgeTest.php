<?php

namespace Brera\PoC;

class EdgeToEdgeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function edgeToEdge()
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
}
