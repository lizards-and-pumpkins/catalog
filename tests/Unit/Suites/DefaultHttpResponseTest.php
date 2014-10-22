<?php

namespace Brera\PoC;

/**
 * @covers \Brera\PoC\DefaultHttpResponse
 */
class DefaultHttpResponseTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var DefaultHttpResponse
	 */
	private $defaultHttpResponse;

	public function setUp()
	{
		$this->defaultHttpResponse = new DefaultHttpResponse();
	}

	/**
	 * @test
	 */
	public function itShouldSetAndRetrieveABody()
	{
		$body = 'dummy';

		$this->defaultHttpResponse->setBody($body);
		$result = $this->defaultHttpResponse->getBody();

		$this->assertEquals($body, $result);
	}

	/**
	 * @test
	 */
	public function itShouldEchoTheBody()
	{
		$body = 'dummy';

		$this->defaultHttpResponse->setBody($body);
		$this->defaultHttpResponse->send();

		$this->expectOutputString($body);
	}
}
