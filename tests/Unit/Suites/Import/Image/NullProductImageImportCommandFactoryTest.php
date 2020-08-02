<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Image\NullProductImageImportCommandFactory
 */
class NullProductImageImportCommandFactoryTest extends TestCase
{
    /**
     * @var NullProductImageImportCommandFactory
     */
    private $factory;

    final protected function setUp(): void
    {
        $this->factory = new NullProductImageImportCommandFactory();
    }

    public function testItImplementsTheProductImportCommandFactoryInterface(): void
    {
        $this->assertInstanceOf(ProductImageImportCommandFactory::class, $this->factory);
    }

    public function testItReturnsNoCommands(): void
    {
        $stubDataVersion = $this->createMock(DataVersion::class);
        $this->assertSame([], $this->factory->createProductImageImportCommands('image.jpg', $stubDataVersion));
    }
}
