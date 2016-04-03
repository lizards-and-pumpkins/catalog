<?php

namespace LizardsAndPumpkins\Import\Image;

use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\TestFileFixtureTrait;

/**
 * @covers \LizardsAndPumpkins\Import\Image\UpdatingProductImageImportCommandFactory
 * @uses   \LizardsAndPumpkins\Import\Image\AddImageCommand
 */
class UpdatingProductImageImportCommandFactoryTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var UpdatingProductImageImportCommandFactory
     */
    private $factory;

    /**
     * @param string $className
     * @param mixed[] $array
     */
    private function assertContainsInstanceOf($className, array $array)
    {
        $found = array_reduce($array, function ($found, $value) use ($className) {
            return $found || $value instanceof $className;
        }, false);
        $this->assertTrue($found, sprintf('Failed asserting that the array contains an instance of "%s"', $className));
    }

    protected function setUp()
    {
        $this->factory = new UpdatingProductImageImportCommandFactory();
    }

    public function testItImplementsTheProductImageImportCommandFactoryInterface()
    {
        $this->assertInstanceOf(ProductImageImportCommandFactory::class, $this->factory);
    }

    public function testItReturnsAnAddImageCommand()
    {
        $imageFilePath = $this->getUniqueTempDir() . '/image.jpg';
        $this->createFixtureFile($imageFilePath, '');

        $stubDataVersion = $this->getMock(DataVersion::class, [], [], '', false);
        $commands = $this->factory->createProductImageImportCommands($imageFilePath, $stubDataVersion);
        $this->assertInternalType('array', $commands);
        $this->assertContainsOnlyInstancesOf(Command::class, $commands);
        $this->assertContainsInstanceOf(AddImageCommand::class, $commands);
    }
}
