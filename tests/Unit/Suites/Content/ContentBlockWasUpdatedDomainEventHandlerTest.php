<?php

namespace Brera\Content;

use Brera\Context\ContextSource;
use Brera\DomainEventHandler;

/**
 * @covers \Brera\Content\ContentBlockWasUpdatedDomainEventHandler
 */
class ContentBlockWasUpdatedDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContentBlockWasUpdatedDomainEvent|\PHPUnit_Framework_MockObject_MockObject
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
        $this->mockDomainEvent = $this->getMock(ContentBlockWasUpdatedDomainEvent::class, [], [], '', false);
        $stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $this->mockProjector = $this->getMock(ContentBlockProjector::class, [], [], '', false);

        $this->domainEventHandler = new ContentBlockWasUpdatedDomainEventHandler(
            $this->mockDomainEvent,
            $stubContextSource,
            $this->mockProjector
        );
    }

    public function testDomainEventHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEventHandler::class, $this->domainEventHandler);
    }

    public function testContentBlockProjectorIsTriggered()
    {
        $stubContentBlockSource = $this->getMock(ContentBlockSource::class, [], [], '', false);
        $this->mockDomainEvent->method('getContentBlockSource')->willReturn($stubContentBlockSource);

        $this->mockProjector->expects($this->once())->method('project');

        $this->domainEventHandler->process();
    }
}
