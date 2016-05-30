<?php

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\TestFileFixtureTrait;

/**
 * @covers \LizardsAndPumpkins\Import\Image\UpdatingProductImageImportCommandFactory
 */
class UpdatingProductImageImportCommandFactoryTest extends \PHPUnit_Framework_TestCase
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
        $stubDataVersion = $this->getMock(DataVersion::class, [], [], '', false);
        $stubDataVersion->method('__toString')->willReturn('123');
        $expectedPayload = json_encode(['file_path' => $imageFilePath, 'data_version' => (string) $stubDataVersion]);
            
        $commands = $this->factory->createProductImageImportCommands($imageFilePath, $stubDataVersion);
        
        $this->assertInternalType('array', $commands);
        array_map(function (array $commandData) use ($expectedPayload) {
            if (! isset($commandData['name']) || 'add_image' !== $commandData['name']) {
                $this->fail('"name" array record must contain the command name "add_image"');
            }
            
            if (! isset($commandData['payload']) || $commandData['payload'] !== $expectedPayload) {
                $this->fail('"payload" array record must contain payload "' . compact('expectedPayload') . '"');
            }
        }, $commands);
    }
}
