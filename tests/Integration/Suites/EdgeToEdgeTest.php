<?php

namespace Brera\PoC\Tests\Integration;

use Brera\PoC\Product\PoCSku;
use Brera\PoC\Product\ProductId;
use Brera\PoC\PoCMasterFactory;
use Brera\PoC\IntegrationTestFactory;
use Brera\PoC\Http\HttpUrl;
use Brera\PoC\Http\HttpRequest;
use Brera\PoC\FrontendFactory;
use Brera\PoC\PoCWebFront;
use Brera\PoC\ProductImportDomainEvent;
use Brera\Poc\HardcodedProductDetailViewSnippetKeyGenerator;

class EdgeToEdgeTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
    public function importProductDomainEventShouldRenderAProduct()
    {
        $factory = new PoCMasterFactory();
        $factory->register(new IntegrationTestFactory());

        $sku = PoCSku::fromString('118235-251');
        $productId = ProductId::fromSku($sku);
        $productName = 'LED Arm-Signallampe';
        
        $xml = file_get_contents('../../doc/example-simple-product.xml');

        $queue = $factory->getEventQueue();
        $queue->add(new ProductImportDomainEvent($xml));
        
        $consumer = $factory->createDomainEventConsumer();
        $numberOfMessages = 1;
        $consumer->process($numberOfMessages);
        
        $reader = $factory->createDataPoolReader();
        /** @var HardcodedProductDetailViewSnippetKeyGenerator $keyGenerator */
        $keyGenerator = $factory->createProductDetailViewSnippetKeyGenerator();
        $environment = $factory->getEnvironmentBuilder()->createEnvironmentFromXml($xml);
        $html = $reader->getSnippet($keyGenerator->getKeyForEnvironment($productId, $environment));
        //$html = $reader->getPoCProductHtml($productId);

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

        $sku = PoCSku::fromString('test');
        $productId = ProductId::fromSku($sku);

        $factory = new PoCMasterFactory();
        $factory->register(new FrontendFactory());
        $factory->register(new IntegrationTestFactory());

        $dataPoolWriter = $factory->createDataPoolWriter();
        $dataPoolWriter->setProductIdBySeoUrl($productId, $httpUrl);
        $dataPoolWriter->setPoCProductHtml($productId, $html);

        $website = new PoCWebFront($request, $factory);
        $response = $website->run(false);

        $this->assertContains($html, $response->getBody());
    }
}
