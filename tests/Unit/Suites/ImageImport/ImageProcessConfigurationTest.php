<?php

namespace Brera\ImageImport;

/**
 * @covers \Brera\ImageImport\ImageProcessConfiguration
 */
class ImageProcessConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ImageProcessConfiguration
     */
    private $configuration;
    /**
     * @var ImageProcessCommandSequence[]
     */
    private $commandStubs = array();

    /**
     * @var string
     */
    private $targetDirectory;

    protected function setUp()
    {
        $this->targetDirectory = '/tmp';
        $stubCommand = $this->getMock(ImageProcessCommandSequence::class, [], [], '', false);
        $stubCommand2 = $this->getMock(ImageProcessCommandSequence::class, [], [], '', false);
        array_push($this->commandStubs, $stubCommand);
        array_push($this->commandStubs, $stubCommand2);
        $this->configuration = new ImageProcessConfiguration(
            array($stubCommand, $stubCommand2),
            $this->targetDirectory
        );
    }

    /**
     * @test
     */
    public function itShouldReturnImageProcessCommands()
    {
        $this->assertInstanceOf(\Traversable::class, $this->configuration);
    }

    /**
     * @test
     */
    public function itShouldReturnTargetDirectory()
    {
        $this->assertEquals($this->targetDirectory, $this->configuration->getTargetDirectory());
    }

    /**
     * @test
     * @expectedException \Brera\ImageImport\InvalidConfigurationException
     */
    public function itShouldThrowAnExceptionWhenTargetDirectoryIsNotWritable()
    {
        $targetDirectory = 'unexisting-not-writeable-directory';
        $stubCommand = $this->getMock(ImageProcessCommandSequence::class, [], [], '', false);
        array_push($this->commandStubs, $stubCommand);
        $this->configuration = new ImageProcessConfiguration(
            array($stubCommand),
            $targetDirectory
        );
    }

    /**
     * @test
     */
    public function itShouldReturnWhatIsPassedWithConstructor()
    {
        foreach ($this->configuration as $command) {
            $this->assertSame(array_shift($this->commandStubs), $command);
        }
    }
}
