<?php

namespace Brera\Tests\Integration;

use Brera\Environment\EnvironmentSource;
use Brera\IntegrationTestFactory;
use Brera\PageBuilder;
use Brera\PageKeyGenerator;
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
use Brera\SnippetResult;
use Brera\SnippetResultList;

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

		/* Temporary hack to mock snippet list */
		$snippetResult = SnippetResult::create('_led_arm_signallampe_1_l', '[]');
		$snippetResultList = new SnippetResultList();
		$snippetResultList->add($snippetResult);
		$dataPoolWriter = $factory->createDataPoolWriter();
		$dataPoolWriter->writeSnippetResultList($snippetResultList);

		$xml = file_get_contents(__DIR__ . '/../../shared-fixture/product.xml');

		$queue = $factory->getEventQueue();
		$queue->add(new CatalogImportDomainEvent($xml));

		$consumer = $factory->createDomainEventConsumer();
		$numberOfMessages = 3;
		$consumer->process($numberOfMessages);

		/** @var EnvironmentSource $environmentSource */
		$environmentSource = $factory->createEnvironmentSourceBuilder()->createFromXml($xml);
		$environment = $environmentSource->extractEnvironments(['version'])[0];
		$url = HttpUrl::fromString('http://example.com/led_arm_signallampe');

		$pageKeyGenerator = new PageKeyGenerator($environment);
		$dataPoolReader = $factory->createDataPoolReader();

		$pageBuilder = new PageBuilder($pageKeyGenerator, $dataPoolReader);
		$page = $pageBuilder->buildPage($url);
		$html = $page->getBody();

		$this->assertContains('118235-251', $html);
		$this->assertContains('LED Arm-Signallampe', $html);
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
