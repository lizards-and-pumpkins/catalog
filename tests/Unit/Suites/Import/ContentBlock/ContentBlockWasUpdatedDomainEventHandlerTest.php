<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Messaging\Event\DomainEventHandler;
use LizardsAndPumpkins\Messaging\Queue\Message;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEventHandler
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockId
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockWasUpdatedDomainEvent
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 */
class ContentBlockWasUpdatedDomainEventHandlerTest extends TestCase
{
    /**
     * @var ContentBlockProjector|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockProjector;

    /**
     * @var ContentBlockWasUpdatedDomainEventHandler
     */
    private $domainEventHandler;

    /**
     * @var Message
     */
    private $testMessage;

    protected function setUp()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $dummyContext */
        $dummyContext = $this->createMock(Context::class);
        $dummyContext->method('jsonSerialize')->willReturn([]);
        $testContentBlockSource = new ContentBlockSource(ContentBlockId::fromString('foo'), '', $dummyContext, []);
        $this->testMessage = (new ContentBlockWasUpdatedDomainEvent($testContentBlockSource))->toMessage();
        $this->mockProjector = $this->createMock(ContentBlockProjector::class);

        $this->domainEventHandler = new ContentBlockWasUpdatedDomainEventHandler(
            $this->testMessage,
            $this->mockProjector
        );
    }

    public function testDomainEventHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(DomainEventHandler::class, $this->domainEventHandler);
    }

    public function testContentBlockProjectorIsTriggered()
    {
        $this->mockProjector->expects($this->once())->method('project');
        $this->domainEventHandler->process();
    }
}
