<?php

namespace Brera\Product;

use Brera\Command;

/**
 * @covers \Brera\Product\UpdateProductCommand
 */
class UpdateProductCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductSource|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubProductSource;

    /**
     * @var UpdateProductCommand
     */
    private $command;

    protected function setUp()
    {
        $this->stubProductSource = $this->getMock(ProductSource::class, [], [], '', false);
        $this->command = new UpdateProductCommand($this->stubProductSource);
    }

    public function testCommandInterfaceIsImplemented()
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testProductSourceIsReturned()
    {
        $result = $this->command->getProductSource();
        $this->assertSame($this->stubProductSource, $result);
    }
}
