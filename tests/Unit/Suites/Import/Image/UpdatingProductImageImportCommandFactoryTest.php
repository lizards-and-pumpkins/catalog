<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\TestFileFixtureTrait;
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

    protected function setUp()
    {
        $this->factory = new UpdatingProductImageImportCommandFactory();
    }

    public function testItImplementsTheProductImageImportCommandFactoryInterface()
    {
        $this->assertInstanceOf(ProductImageImportCommandFactory::class, $this->factory);
    }

    public function testItReturnsAddImageCommandData()
    {
        $imageFilePath = $this->getUniqueTempDir() . '/image.jpg';
        $this->createFixtureFile($imageFilePath, '');

        /** @var DataVersion|\PHPUnit_Framework_MockObject_MockObject $stubDataVersion */
        $stubDataVersion = $this->createMock(DataVersion::class);
        $stubDataVersion->method('__toString')->willReturn('123');

        $commands = $this->factory->createProductImageImportCommands($imageFilePath, $stubDataVersion);

        $this->assertInternalType('array', $commands);
        $this->assertContainsOnlyInstancesOf(AddImageCommand::class, $commands);
    }
}
