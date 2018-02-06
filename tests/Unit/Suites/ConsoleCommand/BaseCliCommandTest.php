<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ConsoleCommand;

use League\CLImate\Argument\Manager as ArgumentManager;
use League\CLImate\CLImate;
use League\CLImate\Util\Output as CliOutput;
use LizardsAndPumpkins\ConsoleCommand\TestDouble\StubCliCommand;
use PHPUnit\Framework\MockObject\Invocation\ObjectInvocation;
use PHPUnit\Framework\MockObject\Matcher\AnyInvokedCount;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ConsoleCommand\BaseCliCommand
 */
class BaseCliCommandTest extends TestCase
{
    /**
     * @var CLImate|\PHPUnit_Framework_MockObject_MockObject
     */
    private $climate;

    /**
     * @var StubCliCommand
     */
    private $cliCommand;

    /**
     * @var AnyInvokedCount
     */
    private $writeOutputSpy;
    
    /**
     * @param string $argumentName
     * @param mixed $value
     */
    private function setArgumentValue(string $argumentName, $value)
    {
        /** @var ArgumentManager|\PHPUnit_Framework_MockObject_MockObject $arguments */
        $arguments = $this->climate->arguments;
        $arguments->method('get')->willReturnMap([[$argumentName, $value]]);
    }

    private function assertStringWasOutput(string $expectedString)
    {
        $callCountWithMatchingStringParam = array_map(function (ObjectInvocation $invocation) use ($expectedString) {
            return intval($this->checkStringMatchesIgnoreCtrlChars($invocation->getParameters()[0], $expectedString));
        }, $this->writeOutputSpy->getInvocations());

        $message = sprintf('The expected string was not output: "%s"', $expectedString);
        $this->assertTrue(array_sum($callCountWithMatchingStringParam) > 0, $message);
    }

    private function checkStringMatchesIgnoreCtrlChars(string $haystack, string $needle) : bool
    {
        return false !== strpos($haystack, $needle);
    }

    public function setUp()
    {
        $this->climate = new CLImate();

        $this->writeOutputSpy = new AnyInvokedCount();
        $mockOutput = $this->createMock(CliOutput::class);
        $mockOutput->expects($this->writeOutputSpy)->method('write');
        $this->climate->output = $mockOutput;

        $this->climate->arguments = $this->getMockBuilder(ArgumentManager::class)
            ->setMethods(['get', 'parse'])
            ->getMock();

        $this->cliCommand = new StubCliCommand($this->climate);
    }

    public function testItReturnsTheSetCLImateInstance()
    {
        $climate = new CLImate();
        $this->cliCommand->publicTestSetCLImate($climate);
        $this->assertSame($climate, $this->cliCommand->publicTestGetCLImate());
    }

    public function testItCallsTheHookMethodsInTheRightOrder()
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
    
    public function testItDelegatesOutputToClimate()
    {
        $testOutputString = 'Please output this string';
        $this->cliCommand->publicTestOutput($testOutputString);
        $this->assertStringWasOutput($testOutputString);
    }

    public function testItReturnsADefaultCLImateInstance()
    {
        $property = new \ReflectionProperty(BaseCliCommand::class, 'climate');
        $property->setAccessible(true);
        $property->setValue($this->cliCommand, null);
        $property->setAccessible(false);
        $this->assertInstanceOf(CLImate::class, $this->cliCommand->publicTestGetCLImate());
    }

    public function testItShowsTheUsageHelp()
    {
        $this->setArgumentValue('help', true);
        $this->cliCommand->run();
        $this->assertStringWasOutput('Usage:');
        $this->assertFalse(in_array('execute', $this->cliCommand->methodCalls));
    }

    public function testRemovesTheCommandNameFromTheArgumentVectorPassedToClimate()
    {
        /** @var \PHPUnit_Framework_MockObject_MockObject $mock */
        $mock = $this->climate->arguments;
        $mock->expects($this->once())->method('parse')->with(['bin/lp qux:command', '--bar', 'baz']);
        $this->cliCommand->publicSetArgumentVector(['bin/lp', 'qux:command', '--bar', 'baz']);
        $this->cliCommand->run();
    }
}
