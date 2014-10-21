<?php

namespace Brera\PoC;

/**
 * @covers \Brera\PoC\UnableToFindDomainEventHandler
 */
class UnableToFindDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function itShouldExtendLogicException()
	{
		$exception = new UnableToFindDomainEventHandler();
		$this->assertInstanceOf(\LogicException::class, $exception);
	}
}
