<?php

namespace Brera;

/**
 * @covers \Brera\RootTemplateChangedDomainEventHandler
 */
class RootTemplateChangedDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldTriggerProjection()
    {
        $stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);
        $stubRootSnippetSourceList = $this->getMock(RootSnippetSourceList::class, [], [], '', false);

        $mockRootTemplateChangedDomainEvent = $this->getMock(RootTemplateChangedDomainEvent::class, [], [], '', false);
        $mockRootTemplateChangedDomainEvent->expects($this->once())
            ->method('getLayoutHandle');

        $mockRootSnippetSourceBuilder = $this->getMock(RootSnippetSourceBuilder::class, [], [], '', false);
        $mockRootSnippetSourceBuilder->expects($this->once())
            ->method('createFromXml')
            ->willReturn($stubRootSnippetSourceList);

        $mockProjector = $this->getMock(RootSnippetProjector::class, [], [], '', false);
        $mockProjector->expects($this->once())
            ->method('project')
            ->with($stubRootSnippetSourceList, $stubContextSource);

        (new RootTemplateChangedDomainEventHandler(
            $mockRootTemplateChangedDomainEvent,
            $mockRootSnippetSourceBuilder,
            $stubContextSource,
            $mockProjector
        ))->process();
    }
}
