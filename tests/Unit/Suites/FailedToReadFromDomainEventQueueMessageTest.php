<?php

namespace Brera;

/**
 * @covers \Brera\FailedToReadFromDomainEventQueueMessage
 */
class FailedToReadFromDomainEventQueueMessageTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @test
	 */
	public function itShouldReturnAnException()
	{
		$stubException = $this->getMock(\Exception::class);
		$message = new FailedToReadFromDomainEventQueueMessage($stubException);
		$result = $message->getException();

		$this->assertInstanceOf(\Exception::class, $result);
	}
}
