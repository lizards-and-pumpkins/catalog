<?php

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Import\Image\Exception\NoAddImageCommandMessageException;
use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Messaging\Queue\Message;

/**
 * @covers \LizardsAndPumpkins\Import\Image\AddImageCommandHandler
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductId
 * @uses   \LizardsAndPumpkins\Import\Product\SimpleProduct
 * @uses   \LizardsAndPumpkins\Import\Tax\ProductTaxClass
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 */
class AddImageCommandHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DomainEventQueue|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDomainEventQueue;

    /**
     * @var AddImageCommandHandler
     */
    private $commandHandler;

    protected function setUp()
    {
        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubCommand */
        $stubCommand = $this->getMock(Message::class, [], [], '', false);
        $stubCommand->method('getName')->willReturn('add_image_command');
        $testPayload = json_encode(['file_path' => '/test/foo.jpg', 'data_version' => 'defg']);
        $stubCommand->method('getPayload')->willReturn($testPayload);

        $this->mockDomainEventQueue = $this->getMock(DomainEventQueue::class, [], [], '', false);

        $this->commandHandler = new AddImageCommandHandler($stubCommand, $this->mockDomainEventQueue);
    }

    public function testCommandHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(CommandHandler::class, $this->commandHandler);
    }

    public function testThrowsExceptionForInvalidCommandNames()
    {
        $this->expectException(NoAddImageCommandMessageException::class);
        $this->expectExceptionMessage('Expected "add_image" command, got "foo_command"');

        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubCommand */
        $stubCommand = $this->getMock(Message::class, [], [], '', false);
        $stubCommand->method('getName')->willReturn('foo_command');

        new AddImageCommandHandler($stubCommand, $this->mockDomainEventQueue);
    }

    public function testImageWasAddedDomainEventIsEmitted()
    {
        $this->mockDomainEventQueue->expects($this->once())->method('addVersioned')
            ->with('image_was_added', $this->isType('string'), $this->anything());

        $this->commandHandler->process();
    }
}
