<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Util\Config;

use LizardsAndPumpkins\Util\Config\Exception\EnvironmentConfigKeyIsEmptyException;
use LizardsAndPumpkins\Util\Config\Exception\EnvironmentConfigKeyIsNotSetException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Util\Config\EnvironmentConfigReader
 */
class EnvironmentConfigReaderTest extends TestCase
{
    public function testItReturnsAnEnvironmentConfigReaderInstance(): void
    {
        $this->assertInstanceOf(EnvironmentConfigReader::class, EnvironmentConfigReader::fromGlobalState());
        $this->assertInstanceOf(EnvironmentConfigReader::class, EnvironmentConfigReader::fromArray([]));
    }

    public function testTheHasMethodThrowsAnExceptionIfTheGivenKeyIsNotAString(): void
    {
        $this->expectException(\TypeError::class);
        EnvironmentConfigReader::fromArray([])->has(123);
    }


    public function testTheHasMethodThrowsAnExceptionIfTheGivenKeyIsEmpty(): void
    {
        $this->expectException(EnvironmentConfigKeyIsEmptyException::class);
        $this->expectExceptionMessage('The given environment configuration key is empty.');
        EnvironmentConfigReader::fromArray([])->has('');
    }

    public function testTheHasMethodReturnsFalseIfAGivenKeyIsNotSet(): void
    {
        $this->assertFalse(EnvironmentConfigReader::fromArray([])->has('not-here'));
    }

    public function testTheHasMethodReturnsTrueIfAGivenKeyIsSet(): void
    {
        $environmentConfig = ['LP_TEST' => ''];
        $this->assertTrue(EnvironmentConfigReader::fromArray($environmentConfig)->has('test'));
    }

    public function testGetMethodThrowsAnExceptionIfAGivenKeyIsNotSet(): void
    {
        $this->expectException(EnvironmentConfigKeyIsNotSetException::class);
        EnvironmentConfigReader::fromArray([])->get('not-here');
    }

    /**
     * @dataProvider emptyConfigKeyProvider
     */
    public function testTheGetMethodThrowsAnExceptionIfTheGivenKeyIsEmpty(string $emptyConfigKey): void
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

    public function testTheGetMethodReturnsTheValueFromTheEnvironmentMethodIfPresent(): void
    {
        $testConfigValue = 'the-value';
        $environmentConfig = ['LP_THE-KEY' => $testConfigValue];
        $this->assertSame($testConfigValue, EnvironmentConfigReader::fromArray($environmentConfig)->get('the-key'));
    }

    public function testItRemovesSpacesFromTheConfigKey(): void
    {
        $testConfigValue = 'another-value';
        $environmentConfig = ['LP_SPACES' => $testConfigValue];
        $this->assertSame($testConfigValue, EnvironmentConfigReader::fromArray($environmentConfig)->get('  spa ces '));
    }
}
