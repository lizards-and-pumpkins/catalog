<?php


namespace Brera;

/**
 * @covers \Brera\EnvironmentBuilder
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
     */
    public function itShouldReturnEnvironmentsForGiveParts()
    {
        $environments = [
            [VersionedEnvironment::CODE => 1],
        ];
        $result = $this->builder->getEnvironments($environments);
        $this->assertCount(1, $result);
        $this->assertContainsOnlyInstancesOf(Environment::class, $result);
    }
}
