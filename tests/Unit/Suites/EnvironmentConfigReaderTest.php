<?php


namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Exception\EnvironmentConfigKeyIsEmptyException;
use LizardsAndPumpkins\Exception\EnvironmentConfigKeyIsNotAStringException;

/**
 * @covers \LizardsAndPumpkins\EnvironmentConfigReader
 */
class EnvironmentConfigReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testItReturnsAnEnvironmentConfigReaderInstance()
    {
        $this->assertInstanceOf(EnvironmentConfigReader::class, EnvironmentConfigReader::fromGlobalState());
        $this->assertInstanceOf(EnvironmentConfigReader::class, EnvironmentConfigReader::fromArray([]));
    }

    public function testTheHasMethodThrowsAnExceptionIfTheGivenKeyIsNotAString()
    {
        $this->setExpectedException(
            EnvironmentConfigKeyIsNotAStringException::class,
            'The given environment configuration key is not a string: "'
        );
        EnvironmentConfigReader::fromArray([])->has(123);
    }

    public function testTheHasMethodThrowsAnExceptionIfTheGivenKeyIsEmpty()
    {
        $this->setExpectedException(
            EnvironmentConfigKeyIsEmptyException::class,
            'The given environment configuration key is empty.'
        );
        EnvironmentConfigReader::fromArray([])->has('');
    }

    public function testTheHasMethodReturnsFalseIfAGivenKeyIsNotSet()
    {
        $this->assertFalse(EnvironmentConfigReader::fromArray([])->has('not-here'));
    }

    public function testTheHasMethodReturnsTrueIfAGivenKeyIsSet()
    {
        $environmentConfig = ['LP_TEST' => ''];
        $this->assertTrue(EnvironmentConfigReader::fromArray($environmentConfig)->has('test'));
    }

    public function testTheGetMethodThrowsAnExceptionIfTheGivenKeyIsNotAString()
    {
        $this->setExpectedException(
            EnvironmentConfigKeyIsNotAStringException::class,
            'The given environment configuration key is not a string: "'
        );
        EnvironmentConfigReader::fromArray([])->get(123);
    }

    public function testTheGetMethodThrowsAnExceptionIfTheGivenKeyIsEmpty()
    {
        $this->setExpectedException(
            EnvironmentConfigKeyIsEmptyException::class,
            'The given environment configuration key is empty.'
        );
        EnvironmentConfigReader::fromArray([])->get('');
    }

    public function testTheGetMethodReturnsANullValueIfAGivenConfigKeyIsNotSet()
    {
        $this->assertNull(EnvironmentConfigReader::fromArray([])->get('not-here'));
    }

    public function testTheGetMethodReturnsTheValueFromTheEnvironmentMethodIfPresent()
    {
        $environmentConfig = ['LP_THE-KEY' => 'the-value'];
        $this->assertSame('the-value', EnvironmentConfigReader::fromArray($environmentConfig)->get('the-key'));
    }
}
