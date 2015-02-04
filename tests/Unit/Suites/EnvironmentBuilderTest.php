<?php


namespace Brera;

/**
 * @covers \Brera\EnvironmentBuilder
 * @uses   \Brera\VersionedEnvironment
 * @uses   \Brera\EnvironmentDecorator
 * @uses   \Brera\InvalidTestStubEnvironmentDecorator
 * @uses   \Brera\ValidTestStubEnvironmentDecorator
 */
class EnvironmentBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EnvironmentBuilder
     */
    private $builder;

    protected function setUp()
    {
        $this->builder = new EnvironmentBuilder();
    }

    /**
     * @test
     * @expectedException \Brera\EnvironmentDecoratorNotFoundException
     */
    public function itShouldThrowAnExceptionForNonExistingCode()
    {
        $environments = [
            [VersionedEnvironment::CODE => 1, 'foo' => 'bar'],
        ];
        $this->builder->getEnvironments($environments);
    }

    /**
     * @test
     * @expectedException \Brera\InvalidEnvironmentDecoratorClassException
     */
    public function itShouldThrowExceptionForNonEnvironmentDecoratorClass()
    {
        $environments = [
            [VersionedEnvironment::CODE => 1, 'invalid_test_stub' => 'dummy'],
        ];
        $this->builder->getEnvironments($environments);
    }

    /**
     * @test
     */
    public function itShouldReturnEnvironmentsForGiveParts()
    {
        $environments = [
            [VersionedEnvironment::CODE => 1, 'valid_test_stub' => 'dummy'],
        ];
        $result = $this->builder->getEnvironments($environments);
        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(Environment::class, $result);
    }

    /**
     * @test
     * @dataProvider underscoreCodeDataProvider
     */
    public function itShouldRemoveUnderscoresFromTheKey($testCode, $expected)
    {
        $method = new \ReflectionMethod($this->builder, 'removeUnderscores');
        $method->setAccessible(true);
        $this->assertEquals($expected, $method->invoke($this->builder, $testCode));
    }

    public function underscoreCodeDataProvider()
    {
        return [
            'no underscores' => ['none', 'none'],
            'one underscore' => ['customer_group', 'customerGroup'],
            'three underscores' => ['test_three_underscores', 'testThreeUnderscores'],
            'underscores front' => ['_front', 'Front'],
            'underscores end' => ['end_', 'end'],
            'consecutive underscores' => ['consecutive__underscores', 'consecutiveUnderscores'],
            'consecutive underscores front' => ['__consecutive_underscores', 'ConsecutiveUnderscores'],
            'consecutive underscores end' => ['consecutive_underscores__', 'consecutiveUnderscores'],
        ];
    }
}

class InvalidTestStubEnvironmentDecorator
{

}

class ValidTestStubEnvironmentDecorator extends EnvironmentDecorator
{
    protected function getValueFromEnvironment()
    {
        return '';
    }

    /**
     * @return string
     */
    protected function getCode()
    {
        return 'valid_test_stub';
    }
}
