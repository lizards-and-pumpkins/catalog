<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\RootTemplate;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Projector;
use LizardsAndPumpkins\Import\RootTemplate\Import\TemplateProjectorLocator;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\ProductListing\Import\TemplateRendering\TemplateProjectionData;

/**
 * @covers \LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEvent
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\ProductListing\Import\TemplateRendering\TemplateProjectionData
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

    private function createDomainEventHandler(Message $message) : TemplateWasUpdatedDomainEventHandler
    {
        /** @var TemplateProjectorLocator|\PHPUnit_Framework_MockObject_MockObject $stubTemplateProjectorLocator */
        $stubTemplateProjectorLocator = $this->createMock(TemplateProjectorLocator::class);
        $stubTemplateProjectorLocator->method('getTemplateProjectorForCode')->willReturn($this->mockProjector);

        return new TemplateWasUpdatedDomainEventHandler(
            $message,
            $stubTemplateProjectorLocator
        );
    }

    protected function setUp()
    {
        $dummyDataVersion = DataVersion::fromVersionString('foo');
        $testEvent = new TemplateWasUpdatedDomainEvent('foo template id', 'bar template content', $dummyDataVersion);

        $this->mockProjector = $this->createMock(Projector::class);

        $this->domainEventHandler = $this->createDomainEventHandler($testEvent->toMessage());
    }

    public function testDomainEventHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEventHandler::class, $this->domainEventHandler);
    }

    public function testProjectionIsTriggered()
    {
        $this->mockProjector->expects($this->once())->method('project')
            ->with($this->isInstanceOf(TemplateProjectionData::class));
        $this->domainEventHandler->process();
    }
}
