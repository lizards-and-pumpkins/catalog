<?php

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\Import\ContentBlock\Exception\NoContentBlockWasUpdatedDomainEventMessageException;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;

/**
 * @covers \LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockId
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource
 */
class ContentBlockWasUpdatedDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Message|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDomainEvent;

    /**
     * @var ContentBlockProjector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProjector;

    /**
     * @var ContentBlockWasUpdatedDomainEventHandler
     */
    private $domainEventHandler;

    protected function setUp()
    {
        $this->mockDomainEvent = $this->getMock(Message::class, [], [], '', false);
        $this->mockDomainEvent->method('getName')->willReturn('content_block_was_updated_domain_event');
        $this->mockProjector = $this->getMock(ContentBlockProjector::class, [], [], '', false);

        $this->domainEventHandler = new ContentBlockWasUpdatedDomainEventHandler(
            $this->mockDomainEvent,
            $this->mockProjector
        );
    }

    public function testDomainEventHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEventHandler::class, $this->domainEventHandler);
    }

    public function testThrowsExceptionIfEventNameDoesNotMatch()
    {
        $this->expectException(NoContentBlockWasUpdatedDomainEventMessageException::class);
        $this->expectExceptionMessage('Expected "content_block_was_updated" domain event, got "foo_domain_event"');

        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $mockDomainEvent */
        $mockDomainEvent = $this->getMock(Message::class, [], [], '', false);
        $mockDomainEvent->method('getName')->willReturn('foo_domain_event');
        new ContentBlockWasUpdatedDomainEventHandler($mockDomainEvent, $this->mockProjector);
    }

    public function testContentBlockProjectorIsTriggered()
    {
        $testContentBlockSource = new ContentBlockSource(
            ContentBlockId::fromString('foo bar'),
            'dummy content',
            [],
            []
        );
        $testPayload = json_encode(['source' => $testContentBlockSource->serialize()]);
        $this->mockDomainEvent->method('getPayload')->willReturn($testPayload);

        $this->mockProjector->expects($this->once())->method('project');

        $this->domainEventHandler->process();
    }
}
