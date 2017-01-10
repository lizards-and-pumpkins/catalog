<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Util\Config;

use LizardsAndPumpkins\Util\Config\Exception\EnvironmentConfigKeyIsEmptyException;
use LizardsAndPumpkins\Util\Config\Exception\EnvironmentConfigKeyIsNotSetException;

/**
 * @covers \LizardsAndPumpkins\Util\Config\EnvironmentConfigReader
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
        $this->expectException(\TypeError::class);
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

    public function testGetMethodThrowsAnExceptionIfAGivenKeyIsNotSet()
    {
        $this->expectException(EnvironmentConfigKeyIsNotSetException::class);
        EnvironmentConfigReader::fromArray([])->get('not-here');
    }

    /**
     * @dataProvider emptyConfigKeyProvider
     */
    public function testTheGetMethodThrowsAnExceptionIfTheGivenKeyIsEmpty(string $emptyConfigKey)
    {
        $this->expectException(EnvironmentConfigKeyIsEmptyException::class);
        $this->expectExceptionMessage('The given environment configuration key is empty.');
        EnvironmentConfigReader::fromArray([])->get($emptyConfigKey);
    }

    /**
     * @return array[]
     */
    public function emptyConfigKeyProvider() : array
    {
        return [
            [''],
            ['  '],
        ];
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
