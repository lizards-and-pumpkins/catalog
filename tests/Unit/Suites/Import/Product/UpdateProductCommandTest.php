<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Product\Exception\NoUpdateProductCommandMessageException;
use LizardsAndPumpkins\Import\Product\Image\ProductImageList;
use LizardsAndPumpkins\Import\Tax\ProductTaxClass;
use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Messaging\Queue\Message;

/**
 * @covers \LizardsAndPumpkins\Import\Product\UpdateProductCommand
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductId
 * @uses   \LizardsAndPumpkins\Import\Product\SimpleProduct
 * @uses   \LizardsAndPumpkins\Import\Tax\ProductTaxClass
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\RehydrateableProductTrait
 */
class UpdateProductCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Product
     */
    private $testProduct;

    /**
     * @var UpdateProductCommand
     */
    private $command;

    /**
     * @return Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubContext()
    {
        $stubContext = $this->createMock(Context::class);
        $stubContext->method('jsonSerialize')->willReturn([DataVersion::CONTEXT_CODE => '123']);
        $stubContext->method('getValue')->willReturnMap([
            [DataVersion::CONTEXT_CODE, '123'],
        ]);
        return $stubContext;
    }

    protected function setUp()
    {
        $this->testProduct = new SimpleProduct(
            ProductId::fromString('foo'),
            ProductTaxClass::fromString('bar'),
            new ProductAttributeList(),
            new ProductImageList(),
            $this->createStubContext()
        );

        $this->command = new UpdateProductCommand($this->testProduct);
    }

    public function testCommandInterfaceIsImplemented()
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testProductBuilderIsReturned()
    {
        $this->assertSame($this->testProduct, $this->command->getProduct());
    }

    public function testReturnsAMessageWithUpdateProductName()
    {
        $message = $this->command->toMessage();
        $this->assertInstanceOf(Message::class, $message);
        $this->assertSame(UpdateProductCommand::CODE, $message->getName());
    }

    public function testReturnsMessageWithPayload()
    {
        $expectedPayload = [
            'id'      => (string)$this->testProduct->getId(),
            'product' => json_encode($this->testProduct),
        ];
        $message = $this->command->toMessage();
        $this->assertSame($expectedPayload, $message->getPayload());
    }

    public function testReturnsMessageWithDataVersion()
    {
        $message = $this->command->toMessage();
        $this->assertSame('123', (string)$message->getMetadata()['data_version']);
    }

    public function testReturnsTheDataVersion()
    {
        $expectedVersion = $this->testProduct->getContext()->getValue(DataVersion::CONTEXT_CODE);
        $dataVersion = $this->command->getDataVersion();
        $this->assertInstanceOf(DataVersion::class, $dataVersion);
        $this->assertSame($expectedVersion, (string)$dataVersion);
    }

    public function testExceptionIsThrownDuringAttemptToRehydrateProductFromWrongMessageType()
    {
        $this->expectException(NoUpdateProductCommandMessageException::class);
        $this->expectExceptionMessage('Unable to rehydrate from "foo" queue message, expected "update_product"');

        /** @var ProductAvailability|\PHPUnit_Framework_MockObject_MockObject $stubAvailability */
        $stubAvailability = $this->createMock(ProductAvailability::class);
        $testMessage = Message::withCurrentTime('foo', [], []);

        UpdateProductCommand::rehydrateProduct($testMessage, $stubAvailability);
    }

    public function testCommandCanBeRehydratedFromUpdateProductCommandMessage()
    {
        $testProduct = new SimpleProduct(
            ProductId::fromString('foo'),
            ProductTaxClass::fromString('bar'),
            new ProductAttributeList(),
            new ProductImageList(),
            $this->createStubContext()
        );

        $testCommand = new UpdateProductCommand($testProduct);
        $testMessage = $testCommand->toMessage();

        /** @var ProductAvailability|\PHPUnit_Framework_MockObject_MockObject $stubAvailability */
        $stubAvailability = $this->createMock(ProductAvailability::class);

        $result = UpdateProductCommand::rehydrateProduct($testMessage, $stubAvailability);
        
        $this->assertEquals($testProduct->getId(), $result->getId());
    }
}
