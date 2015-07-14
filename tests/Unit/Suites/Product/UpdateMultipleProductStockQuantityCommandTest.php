<?php

namespace Brera\Product;

use Brera\Command;

/**
 * @covers \Brera\Product\UpdateMultipleProductStockQuantityCommand
 */
class UpdateMultipleProductStockQuantityCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $dummyPayload = 'foo';

    /**
     * @var UpdateMultipleProductStockQuantityCommand
     */
    private $command;

    protected function setUp()
    {
        $this->command = new UpdateMultipleProductStockQuantityCommand($this->dummyPayload);
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
