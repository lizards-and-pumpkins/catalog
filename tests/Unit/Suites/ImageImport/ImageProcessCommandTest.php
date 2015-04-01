<?php

namespace Brera\ImageImport;

class ImageProcessCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ImageProcessCommand
     */
    private $command;

    /**
     * @var mixed[]
     */
    private $instructions;

    protected function setUp()
    {
        $this->instructions = [
            'resize' => array(200, 200),
        ];

        $this->command = ImageProcessCommand::createByArray($this->instructions);
    }

    /**
     * @test
     */
    public function itShouldReturnAProcessCommand()
    {
        $this->assertInstanceOf(ImageProcessCommand::class, $this->command);
    }

    /**
     * @test
     */
    public function itShouldReturnWhatWasPassed()
    {
        $this->assertEquals($this->instructions, $this->command->getInstructions());
    }

    /**
     * @test
     * @expectedException \Brera\ImageImport\InvalidInstructionException
     * @dataProvider unknownInstructionDataProvider
     */
    public function itShouldThrowAnExceptionWhenCreatedWithAnUnknownInstructions($config)
    {
        ImageProcessCommand::createByArray($config);
    }

    /**
     * @return mixed[]
     */
    public function unknownInstructionDataProvider()
    {
        return [
            array(
                ['unknown-command' => ['parameters']],
            ),
            array(
                1,
            ),
            array(
                0.00,
            ),
            array(
                new \stdClass(),
            ),
            array(
                tmpfile(),
            )
        ];
    }

}
