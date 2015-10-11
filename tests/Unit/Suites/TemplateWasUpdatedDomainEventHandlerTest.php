<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\ContextSource;

/**
 * @covers \LizardsAndPumpkins\TemplateWasUpdatedDomainEventHandler
 */
class TemplateWasUpdatedDomainEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Projector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProjector;

    /**
     * @var TemplateWasUpdatedDomainEventHandler
     */
    private $domainEventHandler;

    protected function setUp()
    {
        /** @var TemplateWasUpdatedDomainEvent|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMock(TemplateWasUpdatedDomainEvent::class, [], [], '', false);
        $stubDomainEvent->method('getProjectionSourceData')->willReturn('stub-projection-source-data');

        /** @var ContextSource|\PHPUnit_Framework_MockObject_MockObject $stubContextSource */
        $stubContextSource = $this->getMock(ContextSource::class, [], [], '', false);

        $this->mockProjector = $this->getMock(Projector::class);

        /** @var TemplateProjectorLocator|\PHPUnit_Framework_MockObject_MockObject $stubTemplateProjectorLocator */
        $stubTemplateProjectorLocator = $this->getMock(TemplateProjectorLocator::class, [], [], '', false);
        $stubTemplateProjectorLocator->method('getTemplateProjectorForCode')->willReturn($this->mockProjector);
        
        $this->domainEventHandler = new TemplateWasUpdatedDomainEventHandler(
            $stubDomainEvent,
            $stubContextSource,
            $stubTemplateProjectorLocator
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
