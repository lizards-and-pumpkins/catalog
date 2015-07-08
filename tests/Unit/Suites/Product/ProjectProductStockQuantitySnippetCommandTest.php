<?php

namespace Brera\Product;

use Brera\Command;

/**
 * @covers \Brera\Product\ProjectProductStockQuantitySnippetCommand
 */
class ProjectProductStockQuantitySnippetCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProjectProductStockQuantitySnippetCommand
     */
    private $command;

    /**
     * @var string
     */
    private $dummyPayloadString = 'foo';

    protected function setUp()
    {
        $this->command = new ProjectProductStockQuantitySnippetCommand($this->dummyPayloadString);
    }

    public function testCommandInterfaceIsImplemented()
    {
        $this->assertInstanceOf(Command::class, $this->command);
    }

    public function testPayloadStringIsReturned()
    {
        $result = $this->command->getPayload();
        $this->assertSame($this->dummyPayloadString, $result);
    }
}
