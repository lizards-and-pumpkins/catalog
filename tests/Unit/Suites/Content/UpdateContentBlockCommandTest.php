<?php

namespace Brera\Content;

use Brera\Command;

/**
 * @covers \Brera\Content\UpdateContentBlockCommand
 */
class UpdateContentBlockCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContentBlockId|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContentBlockId;

    /**
     * @var ContentBlockSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContentBlockSource;

    /**
     * @var UpdateContentBlockCommand
     */
    private $command;

    protected function setUp()
    {
        $this->stubContentBlockId = $this->getMock(ContentBlockId::class, [], [], '', false);
        $this->stubContentBlockSource = $this->getMock(ContentBlockSource::class, [], [], '', false);
        $this->command = new UpdateContentBlockCommand($this->stubContentBlockId, $this->stubContentBlockSource);
    }

    public function testCommandInterfaceIsImplemented()
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testContentBlockIdIsReturned()
    {
        $result = $this->command->getContentBlockId();
        $this->assertSame($this->stubContentBlockId, $result);
    }

    public function testContentBlockSourceIsReturned()
    {
        $result = $this->command->getContentBlockSource();
        $this->assertSame($this->stubContentBlockSource, $result);
    }
}
