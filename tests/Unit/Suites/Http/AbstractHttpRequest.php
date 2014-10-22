<?php

namespace Brera\PoC\Http;

class AbstractHttpRequest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function itShouldReturnAUrl()
	{
		$url = 'http://www.example.com/seo-url/';

		$stubHttpUrl = $this->getStubHttpUrl();
		$stubHttpUrl->expects($this->once())
		            ->method('__toString')
		            ->willReturn($url);

		$httpRequest = new HttpPostRequest($stubHttpUrl);
		$result = $httpRequest->getUrl();

		$this->assertEquals($result, $url);
	}

	/**
	 * @test
	 * @expectedException \Brera\PoC\Http\UnsupportedRequestMethodException
	 * @expectedExceptionMessage Unsupported request method: "PUT"
	 */
	public function itShouldThrowUnsupportedRequestMethodException()
	{
		$stubHttpUrl = $this->getStubHttpUrl();

		$result = HttpRequest::fromParameters('PUT', $stubHttpUrl);

		$this->assertInstanceOf(HttpPostRequest::class, $result);

	}

	protected function getStubHttpUrl()
	{
		$stubHttpUrl = $this->getMockBuilder(HttpUrl::class)
		                    ->disableOriginalConstructor()
		                    ->getMock();
		return $stubHttpUrl;
	}
}
