<?php

namespace Brera;

use Brera\Environment\EnvironmentSource;
use Brera\Environment\VersionedEnvironment;
use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\PoCSku;
use Brera\Product\ProductId;
use Brera\Http\HttpUrl;
use Brera\Http\HttpRequest;
use Brera\Product\ProductDetailViewSnippetKeyGenerator;

class EdgeToEdgeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function importProductDomainEventShouldRenderAProduct()
    {
        $factory = $this->prepareMasterFactory();

        $sku = PoCSku::fromString('118235-251');
        $productId = ProductId::fromSku($sku);
        $productName = 'LED Arm-Signallampe';

        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/product.xml');

        $queue = $factory->getEventQueue();
        $queue->add(new CatalogImportDomainEvent($xml));

        $consumer = $factory->createDomainEventConsumer();
        $numberOfMessages = 3;
        $consumer->process($numberOfMessages);
        
        $reader = $factory->createDataPoolReader();
        
        /** @var ProductDetailViewSnippetKeyGenerator $keyGenerator */
        $keyGenerator = $factory->createProductDetailViewSnippetKeyGenerator();
        
        /** @var EnvironmentSource $environmentSource */
        $environmentSource = $factory->createEnvironmentSourceBuilder()->createFromXml($xml);
        $environment = $environmentSource->extractEnvironments(['version', 'website', 'language'])[0];
        
        $key = $keyGenerator->getKeyForEnvironment($productId, $environment);
        
        $html = $reader->getSnippet($key);

        $this->assertContains((string)$sku, $html);
        $this->assertContains($productName, $html);
    }

    /**
     * @test
     */
    public function itShouldMakeAnImportedProductAccessibleFromTheFrontend()
    {
        $dataVersion = '1';
        
        $factory = new PoCMasterFactory();
        $factory->register(new CommonFactory());
        $factory->register(new IntegrationTestFactory());
        $factory->register(new FrontendFactory());

        $xml = file_get_contents(__DIR__ . '/../../shared-fixture/product.xml');

        $queue = $factory->getEventQueue();
        $queue->add(new CatalogImportDomainEvent($xml));

        $consumer = $factory->createDomainEventConsumer();
        $numberOfMessages = 3;
        $consumer->process($numberOfMessages);
        
        //$reader = $factory->createDataPoolReader();
        
        $productUrlKeys = (new XPathParser($xml))->getXmlNodesArrayByXPath('/*/product/attributes/url_key');
        foreach ($productUrlKeys as $urlKey) {
            $environment = $factory->createEnvironmentBuilder()->getEnvironments([
                ['version' => $dataVersion, 'website' => 'ru_de', 'language' => $urlKey['attributes']['language']]
            ])[0];
            
            $httpUrl = HttpUrl::fromString('http://example.com/' . $urlKey['value']);
            $request = HttpRequest::fromParameters('GET', $httpUrl);

            $website = new PoCWebFront($request, $environment, $factory);
            $response = $website->runWithoutSendingResponse();

            $this->assertContains('<body>', $response->getBody());
        }
    }

    /**
     * @return PoCMasterFactory
     */
    private function prepareMasterFactory()
    {
        $factory = new PoCMasterFactory();
        $factory->register(new CommonFactory());
        $factory->register(new IntegrationTestFactory());
        return $factory;
    }
}
