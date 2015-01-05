<?php

namespace Brera\Product;

use Brera\KeyValue\DataPoolReader;
use Brera\Http\HttpRequest;
use Brera\Http\HttpUrl;
use Brera\MasterFactory;

/**
 * @covers \Brera\Product\ProductSeoUrlRouter
 */
class ProductSeoUrlRouterTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var ProductSeoUrlRouter
	 */
	private $productSeoUrlRouter;

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	private $stubDataPoolReader;

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	private $stubMasterFactory;

	protected function setUp()
	{
		$this->stubDataPoolReader = $this->getMockBuilder(DataPoolReader::class)
		                           ->disableOriginalConstructor()
		                           ->getMock();

		$this->stubMasterFactory = $this->getMockBuilder(MasterFactory::class)
		                          ->disableOriginalConstructor()
		                          ->setMethods(['register', 'createProductDetailPage'])
		                          ->getMock();

		$this->productSeoUrlRouter = new ProductSeoUrlRouter($this->stubDataPoolReader, $this->stubMasterFactory);
	}

	/**
	 * @test
	 */
	public function itShouldReturnProductDetailsPage()
	{
		$this->stubDataPoolReader->expects($this->once())
			->method('hasProductSeoUrl')
			->willReturn(true);
		$this->stubDataPoolReader->expects($this->once())
			->method('getProductIdBySeoUrl');

		$this->stubMasterFactory->expects($this->once())
			->method('createProductDetailPage');

		$stubHttpRequest = $this->getStubHttpRequest();
		$this->productSeoUrlRouter->route($stubHttpRequest);
	}

	/**
	 * @test
	 */
	public function itShouldReturnNullIfThereIsNoSeoUrl()
	{
		$this->stubDataPoolReader->expects($this->once())
		                         ->method('hasProductSeoUrl')
		                         ->willReturn(false);

		$stubHttpRequest = $this->getStubHttpRequest();
		$result = $this->productSeoUrlRouter->route($stubHttpRequest);

		$this->assertNull($result);
	}

	private function getStubHttpRequest()
	{
		$stubHttpUrl = $this->getMockBuilder(HttpUrl::class)
		                    ->disableOriginalConstructor()
		                    ->getMock();

		$stubHttpRequest = $this->getMockBuilder(HttpRequest::class)
		                        ->disableOriginalConstructor()
		                        ->getMock();
		$stubHttpRequest->expects($this->any())
		                ->method('getUrl')
		                ->willReturn($stubHttpUrl);

		return $stubHttpRequest;
	}
}
