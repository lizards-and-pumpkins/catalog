<?php

namespace Brera;

/**
 * @covers \Brera\DomainEventHandlerFailedMessage
 */
class DomainEventHandlerFailedMessageTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	private $stubDomainEvent;

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject
	 */
	private $stubException;

	/**
	 * @var DomainEventHandlerFailedMessage
	 */
	private $message;

	protected function setUp()
	{
		$this->stubDomainEvent = $this->getMock(DomainEvent::class);
		$this->stubException = $this->getMock(\Exception::class);

		$this->message = new DomainEventHandlerFailedMessage($this->stubDomainEvent, $this->stubException);
	}

	/**
	 * @test
	 */
	public function itShouldReturnDomainEvent()
	{
		$result = $this->message->getDomainEvent();
		$this->assertInstanceOf(DomainEvent::class, $result);
	}

	/**
	 * @test
	 */
	public function itShouldReturnAnException()
	{
		$result = $this->message->getException();
		$this->assertInstanceOf(\Exception::class, $result);
	}
}
