<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue\Message;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommandHandler
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\UpdateContentBlockCommand
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEvent
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockId
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 */
class UpdateContentBlockCommandHandlerTest extends TestCase
{
    /**
     * @var DomainEventQueue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDomainEventQueue;

    /**
     * @var UpdateContentBlockCommandHandler
     */
    private $commandHandler;

    /**
     * @return Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createDummyContext()
    {
        $dummyContext = $this->createMock(Context::class);
        $dummyContext->method('jsonSerialize')->willReturn([]);

        return $dummyContext;
    }
    
    public function createTestMessage(): Message
    {
        $testContentBlockId = ContentBlockId::fromString('foo bar');
        $testContentBlockSource = new ContentBlockSource($testContentBlockId, '', $this->createDummyContext(), []);
        return (new UpdateContentBlockCommand($testContentBlockSource))->toMessage();
    }

    protected function setUp()
    {
        $this->mockDomainEventQueue = $this->createMock(DomainEventQueue::class);
        $this->commandHandler = new UpdateContentBlockCommandHandler($this->mockDomainEventQueue);
    }

    public function testCommandHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(CommandHandler::class, $this->commandHandler);
    }

    public function testContentBlockWasUpdatedDomainEventIsEmitted()
    {
        $this->mockDomainEventQueue->expects($this->once())->method('add');

        $this->commandHandler->process($this->createTestMessage());
    }
}
