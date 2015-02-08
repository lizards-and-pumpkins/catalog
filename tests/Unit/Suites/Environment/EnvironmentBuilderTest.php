<?php


namespace Brera\Environment;

/**
 * @covers \Brera\Environment\EnvironmentBuilder
 * @uses   \Brera\Environment\VersionedEnvironment
 * @uses   \Brera\Environment\EnvironmentDecorator
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
     * @expectedException \Brera\Environment\EnvironmentDecoratorNotFoundException
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
     * @expectedException \Brera\Environment\InvalidEnvironmentDecoratorClassException
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

    /**
     * @test
     * @expectedException \Brera\Environment\EnvironmentDecoratorNotFoundException
     */
    public function itShouldThrowAnExceptionWhenAddingANonExistentClass()
    {
        $this->builder->registerEnvironmentDecorator('test', 'Non\\Existent\\DecoratorClass');
    }

    /**
     * @test
     * @expectedException \Brera\Environment\InvalidEnvironmentDecoratorClassException
     */
    public function itShouldThrowAnExceptionWhenAddingAnInvalidDecoratorClass()
    {
        $this->builder->registerEnvironmentDecorator('test', InvalidTestStubEnvironmentDecorator::class);
    }

    /**
     * @test
     */
    public function itShouldAllowRegisteringEnvironmentCodesToClasses()
    {
        $this->builder->registerEnvironmentDecorator('test', ValidTestStubEnvironmentDecorator::class);
        $environments = [
            [VersionedEnvironment::CODE => 1, 'test' => 'dummy'],
        ];
        $result = $this->builder->getEnvironments($environments);
        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(Environment::class, $result);
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
