<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Util\FileSystem\TestFileFixtureTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Image\UpdatingProductImageImportCommandFactory
 * @uses   \LizardsAndPumpkins\Import\Image\AddImageCommand
 */
class UpdatingProductImageImportCommandFactoryTest extends TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var UpdatingProductImageImportCommandFactory
     */
    private $factory;

    final protected function setUp(): void
    {
        $this->factory = new UpdatingProductImageImportCommandFactory();
    }

    public function testItImplementsTheProductImageImportCommandFactoryInterface(): void
    {
        $this->assertInstanceOf(ProductImageImportCommandFactory::class, $this->factory);
    }

    public function testItReturnsAddImageCommandData(): void
    {
        $imageFilePath = $this->getUniqueTempDir() . '/image.jpg';
        $this->createFixtureFile($imageFilePath, '');

        /** @var DataVersion|MockObject $stubDataVersion */
        $stubDataVersion = $this->createMock(DataVersion::class);
        $stubDataVersion->method('__toString')->willReturn('123');

        $commands = $this->factory->createProductImageImportCommands($imageFilePath, $stubDataVersion);

        $this->assertIsArray($commands);
        $this->assertContainsOnlyInstancesOf(AddImageCommand::class, $commands);
    }
}
