<?php


namespace LizardsAndPumpkins\Utils;

use League\CLImate\Argument\Manager as ArgumentManager;
use League\CLImate\CLImate;
use League\CLImate\Util\Output as CliOutput;
use LizardsAndPumpkins\EnvironmentConfigReader;
use LizardsAndPumpkins\Utils\Stub\StubCliCommand;

/**
 * @covers \LizardsAndPumpkins\Utils\CliCommandBase
 */
class CliCommandBaseTest extends \PHPUnit_Framework_TestCase
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
     * @var \PHPUnit_Framework_MockObject_Matcher_AnyInvokedCount
     */
    private $writeOutputSpy;

    /**
     * @param string $environmentConfigString
     */
    private function setEnvironmentConfigString($environmentConfigString)
    {
        /** @var ArgumentManager|\PHPUnit_Framework_MockObject_MockObject $arguments */
        $arguments = $this->climate->arguments;
        $arguments->method('get')->with('environmentConfig')->willReturn($environmentConfigString);
    }

    /**
     * @param string $expectedString
     */
    private function assertStringWasOutput($expectedString)
    {
        $callCountWithMatchingStringParam = array_sum(array_map(function ($invocation) use ($expectedString) {
            return intval($expectedString === $invocation->parameters[0]);
        }, $this->writeOutputSpy->getInvocations()));

        $message = sprintf('The expected string was not output: "%s"', $expectedString);
        $this->assertTrue($callCountWithMatchingStringParam > 0, $message);
    }

    public function setUp()
    {
        $this->climate = new CLImate();

        $this->writeOutputSpy = $this->any();
        $this->climate->output = $this->getMock(CliOutput::class);
        $this->climate->output->expects($this->writeOutputSpy)->method('write');

        $this->climate->arguments = $this->getMockBuilder(ArgumentManager::class)
            ->setMethods(['get'])
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

    public function testItSetsTheEnvironmentConfig()
    {
        $this->setEnvironmentConfigString('foo=bar,baz=qux');
        $this->cliCommand->run();
        $this->assertArrayHasKey(EnvironmentConfigReader::ENV_VAR_PREFIX . 'FOO', $_SERVER);
        $this->assertArrayHasKey(EnvironmentConfigReader::ENV_VAR_PREFIX . 'BAZ', $_SERVER);
        $this->assertSame($_SERVER[EnvironmentConfigReader::ENV_VAR_PREFIX . 'FOO'], 'bar');
        $this->assertSame($_SERVER[EnvironmentConfigReader::ENV_VAR_PREFIX . 'BAZ'], 'qux');
    }

    public function testItTrimsTheEnvironmentConfigKeys()
    {
        $this->setEnvironmentConfigString('foo-with-space =bar');
        $this->cliCommand->run();
        $key = EnvironmentConfigReader::ENV_VAR_PREFIX . 'FOO-WITH-SPACE';
        $this->assertArrayHasKey($key, $_SERVER);
    }

    public function testItTrimsTheEnvironmentConfigValues()
    {
        $this->setEnvironmentConfigString('foo= bar with space ');
        $this->cliCommand->run();
        $key = EnvironmentConfigReader::ENV_VAR_PREFIX . 'FOO';
        $this->assertSame($_SERVER[$key], 'bar with space');
    }

    public function testItThrowsAnExceptionIfEnvironmentSettingIsNoKeyValuePair()
    {
        $this->setEnvironmentConfigString('some-setting');
        $this->cliCommand->run();
        $expectedString = 'Environment settings have to be key=value pairs, "=" not found in "some-setting"';
        $this->assertStringWasOutput($expectedString);
    }

    public function testItThrowsAnExceptionIfTheEnvironmentKeyIsEmpty()
    {
        $this->setEnvironmentConfigString('=bar');
        $this->cliCommand->run();
        $this->assertStringWasOutput('Environment settings have to be key=value pairs, key not found in "=bar"');
    }

    public function testItDelegatesOutputToClimate()
    {
        $testOutputString = 'Please output this string';
        $this->cliCommand->publicTestOutput($testOutputString);
        $this->assertStringWasOutput($testOutputString);
    }

    public function testItReturnsADefaultCLImateInstance()
    {
        $property = new \ReflectionProperty(CliCommandBase::class, 'climate');
        $property->setAccessible(true);
        $property->setValue($this->cliCommand, null);
        $property->setAccessible(false);
        $this->assertInstanceOf(CLImate::class, $this->cliCommand->publicTestGetCLImate());
    }
}
