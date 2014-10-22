<?php

namespace Brera\PoC\Http;

require_once __DIR__ . '/AbstractHttpRequest.php';

/**
 * @covers \Brera\PoC\Http\HttpGetRequest
 * @covers \Brera\PoC\Http\HttpRequest
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
