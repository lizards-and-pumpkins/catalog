<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing;

use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Messaging\Queue\Message;
use LizardsAndPumpkins\ProductListing\Exception\NoAddProductListingCommandMessageException;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductListing\AddProductListingCommand
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 * @uses   \LizardsAndPumpkins\ProductListing\Import\ProductListing
 */
class AddProductListingCommandTest extends TestCase
{
    /**
     * @var ProductListing|MockObject
     */
    private $stubProductListing;

    /**
     * @var AddProductListingCommand
     */
    private $command;

    final protected function setUp(): void
    {
        $this->stubProductListing = $this->createMock(ProductListing::class);
        $this->command = new AddProductListingCommand($this->stubProductListing);
    }

    public function testCommandInterFaceIsImplemented(): void
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testProductListingIsReturned(): void
    {
        $this->assertSame($this->stubProductListing, $this->command->getProductListing());
    }

    public function testReturnsMessageWithAddProductListingName(): void
    {
        $this->stubProductListing->method('serialize')->willReturn(serialize($this->stubProductListing));
        $message = $this->command->toMessage();
        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame(AddProductListingCommand::CODE, $message->getName());
    }

    public function testReturnsMessageWithPayload(): void
    {
        $serializedProductListing = serialize($this->stubProductListing);
        $this->stubProductListing->method('serialize')->willReturn($serializedProductListing);
        $message = $this->command->toMessage();
        $this->assertSame(['listing' => $serializedProductListing], $message->getPayload());
    }

    public function testCanBeRehydratedFromMessage(): void
    {
        $this->stubProductListing->method('serialize')->willReturn(serialize($this->stubProductListing));
        $message = $this->command->toMessage();
        $rehydratedCommand = AddProductListingCommand::fromMessage($message);
        $this->assertInstanceOf(AddProductListingCommand::class, $rehydratedCommand);
    }

    public function testThrowsExceptionIfMessageNameDoesNotMatch(): void
    {
        $this->expectException(NoAddProductListingCommandMessageException::class);
        $expectedMessage = 'Unable to rehydrate from "foo bar" queue message, expected "add_product_listing"';
        $this->expectExceptionMessage($expectedMessage);
        AddProductListingCommand::fromMessage(Message::withCurrentTime('foo bar', [], []));
    }
}
