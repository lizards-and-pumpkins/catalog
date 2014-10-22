<?php

namespace Brera\PoC\Http;

require_once __DIR__ . '/AbstractHttpRequest.php';

/**
 * @covers \Brera\PoC\Http\HttpPostRequest
 * @covers \Brera\PoC\Http\HttpRequest
 * @uses \Brera\PoC\Http\HttpUrl
 */
class HttpPostRequestTest extends AbstractHttpRequest
{
	/**
	 * @test
	 */
	public function itShouldReturnAPostRequest()
	{
		$stubHttpUrl = $this->getStubHttpUrl();

		$result = HttpRequest::fromParameters('POST', $stubHttpUrl);

		$this->assertInstanceOf(HttpPostRequest::class, $result);
	}
}
