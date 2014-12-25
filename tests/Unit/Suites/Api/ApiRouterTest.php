<?php

namespace Brera\PoC\Api;

use Brera\PoC\Http\HttpRequest;
use Brera\PoC\Http\HttpUrl;

/**
 * @covers \Brera\PoC\Api\ApiRouter
 * @uses \Brera\PoC\Api\ApiRequestHandler
 */
class ApiRouterTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var ApiRouter
	 */
	private $apiRouter;

	/**
	 * @var ApiRequestHandlerChain|\PHPUnit_Framework_MockObject_MockObject
	 */
	private $stubApiRequestHandlerChain;

	protected function setUp()
	{
		$this->stubApiRequestHandlerChain = $this->getMock(ApiRequestHandlerChain::class);
		$this->apiRouter = new ApiRouter($this->stubApiRequestHandlerChain);
	}

	/**
	 * @test
	 */
	public function itShouldReturnNullIfUrlIsNotLeadByApiPrefix()
	{
		$stubUrl = $this->getMockBuilder(HttpUrl::class)
			->disableOriginalConstructor()
			->getMock();
		$stubUrl->expects($this->once())
			->method('getPath')
			->willReturn('foo/bar/baz');

		$stubHttpRequest = $this->getStubHttpRequest();
		$stubHttpRequest->expects($this->once())
			->method('getUrl')
			->willReturn($stubUrl);

		$this->assertNull($this->apiRouter->route($stubHttpRequest));
	}

	/**
	 * @test
	 */
	public function itShouldReturnNullIfNoApiRequestHandlerFound()
	{
		$stubUrl = $this->getMockBuilder(HttpUrl::class)
		                ->disableOriginalConstructor()
		                ->getMock();
		$stubUrl->expects($this->once())
		        ->method('getPath')
		        ->willReturn('api/foo/bar');

		$stubHttpRequest = $this->getStubHttpRequest();
		$stubHttpRequest->expects($this->once())
		                ->method('getUrl')
		                ->willReturn($stubUrl);

		$this->assertNull($this->apiRouter->route($stubHttpRequest));
	}

	/**
	 * @test
	 */
	public function itShouldReturnApiRequestHandler()
	{
		$stubUrl = $this->getMockBuilder(HttpUrl::class)
		                ->disableOriginalConstructor()
		                ->getMock();
		$stubUrl->expects($this->once())
		        ->method('getPath')
		        ->willReturn('api/foo/bar');

		$stubHttpRequest = $this->getStubHttpRequest();
		$stubHttpRequest->expects($this->once())
		                ->method('getUrl')
		                ->willReturn($stubUrl);

		$stubApiRequestHandler = $this->getMock(ApiRequestHandler::class, array('bar'));

		$this->stubApiRequestHandlerChain->expects($this->once())
			->method('getApiRequestHandler')
			->willReturn($stubApiRequestHandler);

		$result = $this->apiRouter->route($stubHttpRequest);

		$this->assertInstanceOf(ApiRequestHandler::class, $result);
	}

	/**
	 * @return HttpRequest|\PHPUnit_Framework_MockObject_MockObject
	 */
	private function getStubHttpRequest()
	{
		$stubHttpRequest = $this->getMockBuilder(HttpRequest::class)
		                        ->disableOriginalConstructor()
		                        ->getMock();

		return $stubHttpRequest;
	}
}
