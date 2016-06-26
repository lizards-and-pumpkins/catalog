<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\Product\Exception\NoUpdateProductCommandMessageException;
use LizardsAndPumpkins\Import\Product\Image\ProductImageList;
use LizardsAndPumpkins\Import\Tax\ProductTaxClass;
use LizardsAndPumpkins\Messaging\Queue\Message;

/**
 * @covers \LizardsAndPumpkins\Import\Product\UpdateProductCommandBuilder
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Import\Product\ProductId
 * @uses   \LizardsAndPumpkins\Import\Product\RehydrateableProductTrait
 * @uses   \LizardsAndPumpkins\Import\Product\SimpleProduct
 * @uses   \LizardsAndPumpkins\Import\Product\UpdateProductCommand
 * @uses   \LizardsAndPumpkins\Import\Tax\ProductTaxClass
 * @uses   \LizardsAndPumpkins\Messaging\Queue\Message
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageMetadata
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessageName
 * @uses   \LizardsAndPumpkins\Messaging\Queue\MessagePayload
 */
class UpdateProductCommandBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpdateProductCommandBuilder
     */
    private $commandBuilder;

    protected function setUp()
    {
        /** @var ProductAvailability|\PHPUnit_Framework_MockObject_MockObject $productAvailability */
        $productAvailability = $this->createMock(ProductAvailability::class);

        $this->commandBuilder = new UpdateProductCommandBuilder($productAvailability);
    }

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

    public function testCanBeRehydratedFromUpdateProductCommandMessage()
    {
        $testProduct = new SimpleProduct(
            ProductId::fromString('foo'),
            ProductTaxClass::fromString('bar'),
            new ProductAttributeList(),
            new ProductImageList(),
            $this->createStubContext()
        );

        $command = new UpdateProductCommand($testProduct);

        $message = $command->toMessage();
        $rehydratedCommand = $this->commandBuilder->fromMessage($message);
        $this->assertEquals($testProduct->getId(), $rehydratedCommand->getProduct()->getId());
    }

    public function testThrowsExceptionIfMessageNameNotMatches()
    {
        $this->expectException(NoUpdateProductCommandMessageException::class);
        $this->expectExceptionMessage('Unable to rehydrate from "foo" queue message, expected "update_product"');

        $this->commandBuilder->fromMessage(Message::withCurrentTime('foo', [], []));
    }
}
