<?php

namespace Brera\Tests\Integration;

use Brera\Environment\EnvironmentSource;
use Brera\IntegrationTestFactory;
use Brera\Product\CatalogImportDomainEvent;
use Brera\Product\PoCSku;
use Brera\Product\ProductId;
use Brera\PoCMasterFactory;
use Brera\CommonFactory;
use Brera\Http\HttpUrl;
use Brera\Http\HttpRequest;
use Brera\FrontendFactory;
use Brera\PoCWebFront;
use Brera\Product\HardcodedProductDetailViewSnippetKeyGenerator;

class EdgeToEdgeTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function importProductDomainEventShouldRenderAProduct()
	{
		$factory = new PoCMasterFactory();
		$factory->register(new CommonFactory());
		$factory->register(new IntegrationTestFactory());

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
		/** @var HardcodedProductDetailViewSnippetKeyGenerator $keyGenerator */
		$keyGenerator = $factory->createProductDetailViewSnippetKeyGenerator();
		/** @var EnvironmentSource $environmentSource */
		$environmentSource = $factory->createEnvironmentSourceBuilder()->createFromXml($xml);
		$environment = $environmentSource->extractEnvironments(['version'])[0];
		$key = $keyGenerator->getKeyForEnvironment($productId, $environment);
		$html = $reader->getSnippet($key);

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
		$factory->register(new CommonFactory());
		$factory->register(new IntegrationTestFactory());

		$dataPoolWriter = $factory->createDataPoolWriter();
		$dataPoolWriter->setProductIdBySeoUrl($productId, $httpUrl);
		$dataPoolWriter->setPoCProductHtml($productId, $html);

		$website = new PoCWebFront($request, $factory);
		$response = $website->run(false);

		$this->assertContains($html, $response->getBody());
	}
}
