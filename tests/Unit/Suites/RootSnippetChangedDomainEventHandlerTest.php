<?php

namespace Brera;

use Brera\Context\ContextSource;
use Brera\Context\ContextSourceBuilder;

/**
 * @covers \Brera\RootSnippetChangedDomainEventHandler
 */
class RootSnippetChangedDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldTriggerProjection()
    {
        $stubProjectionSourceData = $this->getMock(ProjectionSourceData::class);
        $stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);

        $mockRootSnippetChangedDomainEvent = $this->getMock(RootSnippetChangedDomainEvent::class, [], [], '', false);
        $mockRootSnippetChangedDomainEvent->expects($this->once())
            ->method('getXml');

        $mockProjector = $this->getMock(RootSnippetProjector::class, [], [], '', false);
        $mockProjector->expects($this->once())
            ->method('project');

        $mockContextSourceBuilder = $this->getMock(ContextSourceBuilder::class, [], [], '', false);
        $mockContextSourceBuilder->expects($this->once())
            ->method('createFromXml')
            ->willReturn($stubContextSource);

        (new RootSnippetChangedDomainEventHandler(
            $mockRootSnippetChangedDomainEvent,
            $mockProjector,
            $stubProjectionSourceData,
            $mockContextSourceBuilder
        ))->process();
    }
}
