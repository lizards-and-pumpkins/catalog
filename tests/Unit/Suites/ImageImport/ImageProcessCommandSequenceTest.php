<?php

namespace Brera\ImageImport;

/**
 * @covers \Brera\ImageImport\ImageProcessCommand
 */
class ImageProcessCommandSequenceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ImageProcessCommandSequence
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

        $this->command = ImageProcessCommandSequence::fromArray($this->instructions);
    }

    /**
     * @test
     */
    public function itShouldReturnAProcessCommand()
    {
        $this->assertInstanceOf(ImageProcessCommandSequence::class, $this->command);
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
        ImageProcessCommandSequence::fromArray($config);
    }

    /**
     * @test
     * @expectedException \Brera\ImageImport\InvalidInstructionException
     */
    public function itShouldThrowAnExceptionWhenForbiddenMethodIsUsed()
    {
        $instructions = [
            'resize' => array(200, 200),
            'saveAsFile' => array(),
        ];

        ImageProcessCommandSequence::fromArray($instructions);
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
