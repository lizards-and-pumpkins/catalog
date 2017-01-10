<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\TestFileFixtureTrait;

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
class AddImageCommandHandlerTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var DomainEventQueue|\PHPUnit_Framework_MockObject_MockObject
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

    protected function setUp()
    {
        $fixtureDirectoryPath = $this->getUniqueTempDir();
        $this->imageFilePath = $fixtureDirectoryPath . '/foo.png';
        $this->createFixtureDirectory($fixtureDirectoryPath);
        $this->createFixtureFile($this->imageFilePath, '');

        /** @var Message|\PHPUnit_Framework_MockObject_MockObject $stubMessage */
        $stubMessage = $this->createMock(Message::class);
        $stubMessage->method('getName')->willReturn('add_image');
        $stubMessage->method('getPayload')->willReturn(['file_path' => $this->imageFilePath, 'data_version' => 'defg']);

        $this->mockDomainEventQueue = $this->createMock(DomainEventQueue::class);

        $this->commandHandler = new AddImageCommandHandler($stubMessage, $this->mockDomainEventQueue);
    }

    public function testCommandHandlerInterfaceIsImplemented()
    {
        $this->assertInstanceOf(CommandHandler::class, $this->commandHandler);
    }

    public function testImageWasAddedDomainEventIsEmitted()
    {
        $this->mockDomainEventQueue->expects($this->once())->method('add');

        $this->commandHandler->process();
    }
}
