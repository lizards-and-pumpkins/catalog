<?php

namespace Brera\Product;

use Brera\Command;

/**
 * @covers \Brera\Product\UpdateProductStockQuantityCommand
 */
class UpdateProductStockQuantityCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $dummyPayload = 'foo';

    /**
     * @var UpdateProductStockQuantityCommand
     */
    private $command;

    protected function setUp()
    {
        $this->command = new UpdateProductStockQuantityCommand($this->dummyPayload);
    }

    public function testCommandInterfaceIsImplemented()
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testCommandPayloadIsReturned()
    {
        $result = $this->command->getPayload();
        $this->assertSame($this->dummyPayload, $result);
    }
}
