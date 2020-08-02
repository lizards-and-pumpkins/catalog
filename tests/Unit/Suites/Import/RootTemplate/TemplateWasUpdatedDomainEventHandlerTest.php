<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\RootTemplate;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Projector;
use LizardsAndPumpkins\Import\RootTemplate\Import\TemplateProjectorLocator;
use LizardsAndPumpkins\Import\TemplateRendering\TemplateProjectionData;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEvent
 * @uses   \LizardsAndPumpkins\Import\TemplateRendering\TemplateProjectionData
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 */
class TemplateWasUpdatedDomainEventHandlerTest extends TestCase
{
    /**
     * @var Projector
     */
    private $mockProjector;

    /**
     * @var TemplateWasUpdatedDomainEventHandler
     */
    private $domainEventHandler;

    private function createTestMessage(): Message
    {
        $dummyDataVersion = DataVersion::fromVersionString('foo');
        $testEvent = new TemplateWasUpdatedDomainEvent('foo template id', 'bar template content', $dummyDataVersion);
        return $testEvent->toMessage();
    }

    private function createDomainEventHandler() : TemplateWasUpdatedDomainEventHandler
    {
        /** @var TemplateProjectorLocator|MockObject $stubTemplateProjectorLocator */
        $stubTemplateProjectorLocator = $this->createMock(TemplateProjectorLocator::class);
        $stubTemplateProjectorLocator->method('getTemplateProjectorForCode')->willReturn($this->mockProjector);

        return new TemplateWasUpdatedDomainEventHandler($stubTemplateProjectorLocator);
    }

    final protected function setUp(): void
    {
        $this->mockProjector = $this->createMock(Projector::class);

        $this->domainEventHandler = $this->createDomainEventHandler();
    }

    public function testDomainEventHandlerInterfaceIsImplemented(): void
    {
        $this->assertInstanceOf(DomainEventHandler::class, $this->domainEventHandler);
    }

    public function testProjectionIsTriggered(): void
    {
        $this->mockProjector->expects($this->once())->method('project')
            ->with($this->isInstanceOf(TemplateProjectionData::class));
        $this->domainEventHandler->process($this->createTestMessage());
    }
}
