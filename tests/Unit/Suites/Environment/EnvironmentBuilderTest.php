<?php


namespace Brera\Environment;

use Brera\DataVersion;
use Brera\Http\HttpRequest;

/**
 * @covers \Brera\Environment\EnvironmentBuilder
 * @uses   \Brera\Environment\VersionedEnvironment
 * @uses   \Brera\Environment\EnvironmentDecorator
 * @uses   \Brera\DataVersion
 */
class EnvironmentBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EnvironmentBuilder
     */
    private $builder;

    protected function setUp()
    {
        $this->builder = new EnvironmentBuilder(DataVersion::fromVersionString('1'));
    }

    /**
     * @test
     * @expectedException \Brera\Environment\EnvironmentDecoratorNotFoundException
     */
    public function itShouldThrowAnExceptionForNonExistingCode()
    {
        $environments = ['foo' => 'bar'];
        $this->builder->getEnvironment($environments);
    }

    /**
     * @test
     * @expectedException \Brera\Environment\InvalidEnvironmentDecoratorClassException
     */
    public function itShouldThrowExceptionForNonEnvironmentDecoratorClass()
    {
        $environments = ['stub_invalid_test' => 'dummy'];
        $this->builder->getEnvironment($environments);
    }

    /**
     * @test
     */
    public function itShouldReturnEnvironmentsForGiveParts()
    {
        $environments = [
            ['stub_valid_test' => 'dummy'],
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
        $this->builder->registerEnvironmentDecorator('test', StubInvalidTestEnvironmentDecorator::class);
    }

    /**
     * @test
     */
    public function itShouldAllowRegisteringEnvironmentCodesToClasses()
    {
        $this->builder->registerEnvironmentDecorator('test', StubValidTestEnvironmentDecorator::class);
        $environments = [
            ['test' => 'dummy'],
        ];
        $result = $this->builder->getEnvironments($environments);
        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(Environment::class, $result);
    }

    /**
     * @test
     */
    public function itShouldCreateAnEnvironmentFromARequest()
    {
        $stubRequest = $this->getMockBuilder(HttpRequest::class)
            ->disableOriginalConstructor()
            ->getMock();
        $result = $this->builder->createFromRequest($stubRequest);
        $this->assertInstanceOf(Environment::class, $result);
    }
}
