<?php

namespace LizardsAndPumpkins\Import\RootTemplate;

use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\Import\Projector;
use LizardsAndPumpkins\Import\RootTemplate\Import\TemplateProjectorLocator;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;

/**
 * @covers \LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEvent
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

    /**
     * @param Message $message
     * @return TemplateWasUpdatedDomainEventHandler
     */
    private function createDomainEventHandler(Message $message)
    {
        /** @var TemplateProjectorLocator|\PHPUnit_Framework_MockObject_MockObject $stubTemplateProjectorLocator */
        $stubTemplateProjectorLocator = $this->getMock(TemplateProjectorLocator::class, [], [], '', false);
        $stubTemplateProjectorLocator->method('getTemplateProjectorForCode')->willReturn($this->mockProjector);

        return new TemplateWasUpdatedDomainEventHandler(
            $message,
            $this->createStubContextSource(),
            $stubTemplateProjectorLocator
        );
    }

    /**
     * @return ContextSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubContextSource()
    {
        return $this->getMock(ContextSource::class, [], [], '', false);
    }

    protected function setUp()
    {
        $testPayload = ['id' => 'foo', 'template' => 'buz template content'];
        
        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubDomainEvent */
        $stubDomainEvent = $this->getMock(Message::class, [], [], '', false);
        $stubDomainEvent->method('getName')->willReturn('template_was_updated');
        $stubDomainEvent->method('getPayload')->willReturn(json_encode($testPayload));

        $this->mockProjector = $this->getMock(Projector::class);

        $this->domainEventHandler = $this->createDomainEventHandler($stubDomainEvent);
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
