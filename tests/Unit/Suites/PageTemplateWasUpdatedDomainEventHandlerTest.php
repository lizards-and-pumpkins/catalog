<?php

namespace Brera;

/**
 * @covers \Brera\PageTemplateWasUpdatedDomainEventHandler
 */
class PageTemplateWasUpdatedDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PageTemplateWasUpdatedDomainEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubDomainEvent;

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
        $stubRootSnippetSourceList = $this->getMock(RootSnippetSourceList::class, [], [], '', false);
        $this->stubDomainEvent = $this->getMock(PageTemplateWasUpdatedDomainEvent::class, [], [], '', false);
        $this->stubDomainEvent->method('getRootSnippetSourceList')->willReturn($stubRootSnippetSourceList);

        $stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);
        $this->mockProjector = $this->getMock(RootSnippetProjector::class, [], [], '', false);

        $this->domainEventHandler = new PageTemplateWasUpdatedDomainEventHandler(
            $this->stubDomainEvent,
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
