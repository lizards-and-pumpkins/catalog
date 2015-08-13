<?php

namespace Brera;

use Brera\Context\ContextSource;

/**
 * @covers \Brera\PageTemplateWasUpdatedDomainEventHandler
 */
class PageTemplateWasUpdatedDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RootSnippetProjector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProjector;

    /**
     * @var PageTemplateWasUpdatedDomainEventHandler
     */
    private $domainEventHandler;

    protected function setUp()
    {
        $stubProjectionSourceData = $this->getMock(ProjectionSourceData::class);

        /** @var PageTemplateWasUpdatedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMock(PageTemplateWasUpdatedDomainEvent::class, [], [], '', false);
        $stubDomainEvent->method('getProjectionSourceData')->willReturn($stubProjectionSourceData);

        /** @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $stubContextSource */
        $stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);
        $this->mockProjector = $this->getMock(RootSnippetProjector::class, [], [], '', false);

        $this->domainEventHandler = new PageTemplateWasUpdatedDomainEventHandler(
            $stubDomainEvent,
            $stubContextSource,
            $this->mockProjector
        );
    }

    public function testDomainEventHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEventHandler::class, $this->domainEventHandler);
    }

    public function testProjectionIsTriggered()
    {
        $this->mockProjector->expects($this->once())->method('project');
        $this->domainEventHandler->process();
    }
}
