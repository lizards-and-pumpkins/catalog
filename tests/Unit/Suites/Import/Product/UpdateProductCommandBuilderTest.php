<?php

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Context\SelfContainedContext;
use LizardsAndPumpkins\Import\Product\Image\ProductImageList;
use LizardsAndPumpkins\Import\Tax\ProductTaxClass;

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

    /**
     * @var ProductAvailability|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubAvailability;

    protected function setUp()
    {
        $this->stubAvailability = $this->createMock(ProductAvailability::class);
        $this->commandBuilder = new UpdateProductCommandBuilder($this->stubAvailability);
    }

    public function testCommandBuilderInterfaceIsImplemented()
    {
        $this->assertInstanceOf(CommandBuilder::class, $this->commandBuilder);
    }

    public function testUpdateProductCommandIsReturned()
    {
        $testProduct = new SimpleProduct(
            ProductId::fromString('foo'),
            ProductTaxClass::fromString('bar'),
            new ProductAttributeList(),
            new ProductImageList(),
            SelfContainedContext::fromArray([DataVersion::CONTEXT_CODE => '123']),
            $this->stubAvailability
        );

        $testCommand = new UpdateProductCommand($testProduct);
        $testMessage = $testCommand->toMessage();

        $result = $this->commandBuilder->fromMessage($testMessage);

        $this->assertInstanceOf(UpdateProductCommand::class, $result);
    }
}
