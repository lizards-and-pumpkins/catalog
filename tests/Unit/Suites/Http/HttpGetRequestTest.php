<?php

namespace Brera\Http;

require_once __DIR__ . '/AbstractHttpRequest.php';

/**
 * @covers \Brera\Http\HttpGetRequest
 * @covers \Brera\Http\HttpRequest
 * @uses \Brera\Http\HttpUrl
 */
class HttpGetRequestTest extends AbstractHttpRequest
{
	/**
	 * @test
	 */
	public function itShouldReturnAGetRequest()
	{
		$stubHttpUrl = $this->getStubHttpUrl();

		$result = HttpRequest::fromParameters('GET', $stubHttpUrl);

		$this->assertInstanceOf(HttpGetRequest::class, $result);
	}
}
