<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Util\Factory;

use LizardsAndPumpkins\ConsoleCommand\ConsoleCommandLocator;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Util\Factory\ConsoleCommandFactory
 */
class ConsoleCommandFactoryTest extends TestCase
{
    public function testImplementsTheFactoryInterface()
    {
        $this->assertInstanceOf(Factory::class, new ConsoleCommandFactory());
    }

    public function testReturnsAConsoleCommandLocator()
    {
        $consoleCommandLocator = (new ConsoleCommandFactory())->createConsoleCommandLocator();
        $this->assertInstanceOf(ConsoleCommandLocator::class, $consoleCommandLocator);
    }
}
