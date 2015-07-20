<?php

namespace Brera;

/**
 * @covers \Brera\PageTemplateWasUpdatedDomainEventHandler
 */
class PageTemplateWasUpdatedDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    public function testProjectionIsTriggered()
    {
        $stubContextSource = $this->getMock(SampleContextSource::class, [], [], '', false);
        $stubRootSnippetSourceList = $this->getMock(RootSnippetSourceList::class, [], [], '', false);

        $mockDomainEvent = $this->getMock(PageTemplateWasUpdatedDomainEvent::class, [], [], '', false);

        $mockRootSnippetSourceBuilder = $this->getMock(RootSnippetSourceListBuilder::class, [], [], '', false);
        $mockRootSnippetSourceBuilder->method('createFromXml')->willReturn($stubRootSnippetSourceList);

        $mockProjector = $this->getMock(RootSnippetProjector::class, [], [], '', false);
        $mockProjector->expects($this->once())
            ->method('project')
            ->with($stubRootSnippetSourceList, $stubContextSource);

        (new PageTemplateWasUpdatedDomainEventHandler(
            $mockDomainEvent,
            $mockRootSnippetSourceBuilder,
            $stubContextSource,
            $mockProjector
        ))->process();
    }
}
