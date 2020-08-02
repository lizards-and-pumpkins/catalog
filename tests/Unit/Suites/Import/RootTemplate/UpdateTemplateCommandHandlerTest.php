<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Import\RootTemplate;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\RootTemplate\UpdateTemplateCommandHandler
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\UpdateTemplateCommand
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEvent
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 */
class UpdateTemplateCommandHandlerTest extends TestCase
{
    private $testTemplateId = 'test';

    private $testContent = 'test';

    /**
     * @var DataVersion
     */
    private $testDataVersion;

    /**
     * @var DomainEventQueue|MockObject
     */
    private $mockEventQueue;

    final protected function setUp(): void
    {
        $this->mockEventQueue = $this->createMock(DomainEventQueue::class);
        $this->testDataVersion = DataVersion::fromVersionString('test');
    }

    private function createCommandHandler(): UpdateTemplateCommandHandler
    {
        return new UpdateTemplateCommandHandler($this->mockEventQueue);
    }

    public function testIsACommandHandler(): void
    {
        $this->assertInstanceOf(CommandHandler::class, $this->createCommandHandler());
    }

    public function testEmitsATemplateWasUpdatedEvent(): void
    {
        $this->mockEventQueue->expects($this->once())->method('add')
            ->willReturnCallback(function(TemplateWasUpdatedDomainEvent $event) {
                $this->assertSame($this->testTemplateId, $event->getTemplateId());
                $this->assertSame($this->testContent, $event->getTemplateContent());
                $this->assertSame((string) $this->testDataVersion, (string) $event->getDataVersion());
            });

        $command = new UpdateTemplateCommand($this->testTemplateId, $this->testContent, $this->testDataVersion);
        $this->createCommandHandler()->process($command->toMessage());
    }
}
