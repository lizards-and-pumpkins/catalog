<?php

namespace Brera\Http;

require_once __DIR__ . '/AbstractHttpRequest.php';

/**
 * @covers \Brera\Http\HttpPostRequest
 * @covers \Brera\Http\HttpRequest
 * @uses \Brera\Http\HttpUrl
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
