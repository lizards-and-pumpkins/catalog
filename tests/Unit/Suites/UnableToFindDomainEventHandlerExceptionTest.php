<?php

namespace Brera\PoC;

/**
 * @covers \Brera\PoC\UnableToFindDomainEventHandlerException
 */
class UnableToFindDomainEventHandlerExceptionTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function itShouldExtendLogicException()
	{
		$exception = new UnableToFindDomainEventHandlerException();
		$this->assertInstanceOf(\LogicException::class, $exception);
	}
}
