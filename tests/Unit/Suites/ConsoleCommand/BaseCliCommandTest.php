<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ConsoleCommand;

use League\CLImate\Argument\Manager as ArgumentManager;
use League\CLImate\CLImate;
use League\CLImate\Util\Output as CliOutput;
use LizardsAndPumpkins\ConsoleCommand\TestDouble\StubCliCommand;
use PHPUnit\Framework\Constraint\StringContains;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ConsoleCommand\BaseCliCommand
 */
class BaseCliCommandTest extends TestCase
{
    /**
     * @var CLImate
     */
    private $climate;

    /**
     * @var StubCliCommand
     */
    private $cliCommand;

    /**
     * @var CliOutput|MockObject
     */
    private $mockOutput;

    /**
     * @param string $argumentName
     * @param mixed $value
     */
    private function setArgumentValue(string $argumentName, $value): void
    {
        /** @var ArgumentManager|MockObject $arguments */
        $arguments = $this->climate->arguments;
        $arguments->method('get')->willReturnMap([[$argumentName, $value]]);
    }

    final protected function setUp(): void
    {
        $this->climate = new CLImate();

        $this->mockOutput = $this->createMock(CliOutput::class);
        $this->climate->output = $this->mockOutput;

        $this->climate->arguments = $this->getMockBuilder(ArgumentManager::class)
            ->onlyMethods(['get', 'parse'])
            ->getMock();

        $this->cliCommand = new StubCliCommand($this->climate);
    }

    public function testItReturnsTheSetCLImateInstance(): void
    {
        $climate = new CLImate();
        $this->cliCommand->publicTestSetCLImate($climate);

        $this->assertSame($climate, $this->cliCommand->publicTestGetCLImate());
    }

    public function testItCallsTheHookMethodsInTheRightOrder(): void
    {
        $this->cliCommand->run();
        $expectedCalls = [
            'getCommandLineArgumentsArray',
            'beforeExecute',
            'execute',
            'afterExecute'
        ];
        $this->assertSame($expectedCalls, $this->cliCommand->methodCalls);
    }
    
    public function testItDelegatesOutputToClimate(): void
    {
        $testOutputString = 'Please output this string';
        $this->mockOutput->expects($this->once())->method('write')->with(new StringContains($testOutputString));

        $this->cliCommand->publicTestOutput($testOutputString);
    }

    public function testItReturnsADefaultCLImateInstance(): void
    {
        $property = new \ReflectionProperty(BaseCliCommand::class, 'climate');
        $property->setAccessible(true);
        $property->setValue($this->cliCommand, null);
        $property->setAccessible(false);
        $this->assertInstanceOf(CLImate::class, $this->cliCommand->publicTestGetCLImate());
    }

    public function testItShowsTheUsageHelp(): void
    {
        $this->mockOutput->expects($this->at(1))->method('write')->with(new StringContains('Usage:'));

        $this->setArgumentValue('help', true);
        $this->cliCommand->run();

        $this->assertFalse(in_array('execute', $this->cliCommand->methodCalls));
    }

    public function testRemovesTheCommandNameFromTheArgumentVectorPassedToClimate(): void
    {
        /** @var MockObject $mock */
        $mock = $this->climate->arguments;
        $mock->expects($this->once())->method('parse')->with(['bin/lp qux:command', '--bar', 'baz']);
        $this->cliCommand->publicSetArgumentVector(['bin/lp', 'qux:command', '--bar', 'baz']);
        $this->cliCommand->run();
    }
}
