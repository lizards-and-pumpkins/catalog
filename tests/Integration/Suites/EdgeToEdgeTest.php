<?php

namespace Brera\PoC\Tests\Integration;

require __DIR__ . '/stubs/SkuStub.php';

use Brera\PoC\Integration\stubs\SkuStub;
use Brera\PoC\Product\ProductId;
use Brera\PoC\PoCMasterFactory;
use Brera\PoC\IntegrationTestFactory;
use Brera\PoC\ProductCreatedDomainEvent;
use Brera\PoC\Http\HttpUrl;
use Brera\PoC\Http\HttpRequest;
use Brera\PoC\FrontendFactory;
use Brera\PoC\PoCWebFront;
use Brera\PoC\ProductImportDomainEvent;

/**
 * Class EdgeToEdgeTest
 * @package Brera\PoC
 */
class EdgeToEdgeTest extends \PHPUnit_Framework_TestCase
{
    public function importProductDomainEventShouldRenderAProduct()
    {
        $factory = new PoCMasterFactory();
        $factory->register(new IntegrationTestFactory());


        $sku = new SkuStub('118235-251');
        $productId = ProductId::fromSku($sku);
        $productName = 'LED Armflasher';
        
        $xml = '<product>
  <sku>118235-251</sku>
  <_type>simple</_type>
  <_category>Laufshop/Shop,Laufshop/Herren/Laufzubeh&ouml;r,Laufshop/Damen/Laufzubeh&ouml;r</_category>
  <description>Pro Touch LED Armflasher&lt;br /&gt;&#13;
&lt;br /&gt;&#13;
LED Armflasher mit elastischem Band und Flasher mit variabler Blinkfolge,&#13;
Flasher abnehmbar.&#13;</description>
  <name>LED Armflasher</name>
  <short_description>Pro Touch LED Armflasher Laufzubeh&ouml;r Leuchten Damen,Herren</short_description>
  <price>12.95</price>
  <tax_class_id>5</tax_class_id>
  <status>1</status>
  <weight>1.000</weight>
  <qty>5.00</qty>
  <is_in_stock>1</is_in_stock>
  <backorders>0</backorders>
  <image/>
  <image_label/>
  <small_image/>
  <small_image_label/>
  <thumbnail/>
  <thumbnail_label/>
  <_super_attribute_code/>
  <_super_attribute_option/>
  <_super_attribute_price_corr/>
  <brand>Pro Touch</brand>
  <series>LED Armflasher</series>
  <gender>Damen</gender>
  <product_group>Laufzubeh&ouml;r</product_group>
  <style>Leuchten</style>
  <size/>
  <base_price_amount/>
  <base_price_base_amount/>
  <base_price_base_unit/>
  <base_price_unit/>
  <price_retail>12.95</price_retail>
  <product_360/>
  <size_eu/>
  <yt_link/>
</product>';

        $queue = $factory->getEventQueue();
        $queue->add(new ProductImportDomainEvent($xml));

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

        $website = new PoCWebFront($request, $factory);
        $response = $website->run(false);

        $this->assertContains($html, $response->getBody());
    }
}
