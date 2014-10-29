<?php

namespace Brera\PoC;

use Brera\PoC\Http\HttpRequest;

/**
 * @covers \Brera\PoC\PoCWebFront
 * @covers \Brera\PoC\WebFront
 */
class PoCWebFrontTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var PoCWebFront
	 */
	private $pocWebFront;

	protected function setUp()
	{
		$stubHttpRequest = $this->getMockBuilder(HttpRequest::class)
			->disableOriginalConstructor()
			->getMock();
		$stubMasterFactory = $this->getMock(MasterFactory::class);

		$this->pocWebFront = new PoCWebFront($stubHttpRequest, $stubMasterFactory);
	}

	/**
	 * @test
	 */
	public function itShouldReturnMasterFactory()
	{
		$result = $this->pocWebFront->getMasterFactory();
		$this->assertInstanceOf(MasterFactory::class, $result);
	}
}
