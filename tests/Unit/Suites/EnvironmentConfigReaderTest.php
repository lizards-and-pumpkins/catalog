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
        $this->expectException(EnvironmentConfigKeyIsNotAStringException::class);
        $this->expectExceptionMessage('The given environment configuration key is not a string: "');
        EnvironmentConfigReader::fromArray([])->has(123);
    }

    public function testTheHasMethodThrowsAnExceptionIfTheGivenKeyIsEmpty()
    {
        $this->expectException(EnvironmentConfigKeyIsEmptyException::class);
        $this->expectExceptionMessage('The given environment configuration key is empty.');
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
        $this->expectException(EnvironmentConfigKeyIsNotAStringException::class);
        $this->expectExceptionMessage('The given environment configuration key is not a string: "');
        EnvironmentConfigReader::fromArray([])->get(123);
    }

    /**
     * @param string $emptyConfigKey
     * @dataProvider emptyConfigKeyProvider
     */
    public function testTheGetMethodThrowsAnExceptionIfTheGivenKeyIsEmpty($emptyConfigKey)
    {
        $this->expectException(EnvironmentConfigKeyIsEmptyException::class);
        $this->expectExceptionMessage('The given environment configuration key is empty.');
        EnvironmentConfigReader::fromArray([])->get($emptyConfigKey);
    }

    /**
     * @return array[]
     */
    public function emptyConfigKeyProvider()
    {
        return [
            [''],
            ['  '],
        ];
    }

    public function testTheGetMethodReturnsANullValueIfAGivenConfigKeyIsNotSet()
    {
        $this->assertNull(EnvironmentConfigReader::fromArray([])->get('not-here'));
    }

    public function testTheGetMethodReturnsTheValueFromTheEnvironmentMethodIfPresent()
    {
        $testConfigValue = 'the-value';
        $environmentConfig = ['LP_THE-KEY' => $testConfigValue];
        $this->assertSame($testConfigValue, EnvironmentConfigReader::fromArray($environmentConfig)->get('the-key'));
    }

    public function testItRemovesSpacesFromTheConfigKey()
    {
        $testConfigValue = 'another-value';
        $environmentConfig = ['LP_SPACES' => $testConfigValue];
        $this->assertSame($testConfigValue, EnvironmentConfigReader::fromArray($environmentConfig)->get('  spa ces '));
    }
}
