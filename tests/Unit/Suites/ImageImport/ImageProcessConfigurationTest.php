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
     * @var ImageProcessCommand[]
     */
    private $commandStubs = array();

    protected function setUp()
    {
        $stubCommand = $this->getMock(ImageProcessCommand::class);
        $stubCommand2 = $this->getMock(ImageProcessCommand::class);
        array_push($this->commandStubs, $stubCommand);
        array_push($this->commandStubs, $stubCommand2);
        $this->configuration = new ImageProcessConfiguration(array($stubCommand, $stubCommand2));
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
    public function itShouldReturnWhatIsPassedWithConstructor()
    {
        foreach ($this->configuration as $command) {
            $this->assertSame(array_shift($this->commandStubs), $command);
        }
    }
}
