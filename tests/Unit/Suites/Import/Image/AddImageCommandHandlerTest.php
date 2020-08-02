<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\TestFileFixtureTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Image\AddImageCommandHandler
 * @uses   \LizardsAndPumpkins\Import\Image\ImageWasAddedDomainEvent
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductId
 * @uses   \LizardsAndPumpkins\Import\Product\SimpleProduct
 * @uses   \LizardsAndPumpkins\Import\Tax\ProductTaxClass
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 * @uses   \LizardsAndPumpkins\Import\Image\AddImageCommand
 */
class AddImageCommandHandlerTest extends TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var DomainEventQueue|MockObject
     */
    private $mockDomainEventQueue;

    /**
     * @var string
     */
    private $imageFilePath;

    /**
     * @var AddImageCommandHandler
     */
    private $commandHandler;

    private function createStubMessage(): Message
    {
        /** @var Message|MockObject $stubMessage */
        $stubMessage = $this->createMock(Message::class);
        $stubMessage->method('getName')->willReturn('add_image');
        $stubMessage->method('getPayload')->willReturn(['file_path' => $this->imageFilePath, 'data_version' => 'defg']);
        return $stubMessage;
    }
    
    final protected function setUp(): void
    {
        $fixtureDirectoryPath = $this->getUniqueTempDir();
        $this->imageFilePath = $fixtureDirectoryPath . '/foo.png';
        $this->createFixtureDirectory($fixtureDirectoryPath);
        $this->createFixtureFile($this->imageFilePath, '');

        $this->mockDomainEventQueue = $this->createMock(DomainEventQueue::class);

        $this->commandHandler = new AddImageCommandHandler($this->mockDomainEventQueue);
    }

    public function testCommandHandlerInterfaceIsImplemented(): void
    {
        $this->assertInstanceOf(CommandHandler::class, $this->commandHandler);
    }

    public function testImageWasAddedDomainEventIsEmitted(): void
    {
        $this->mockDomainEventQueue->expects($this->once())->method('add');

        $this->commandHandler->process($this->createStubMessage());
    }
}
