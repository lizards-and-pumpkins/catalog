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
            [VersionedEnvironment::KEY => 1, 'foo' => 'bar'],
        ];
        $result = $this->builder->getEnvironments($environments);
    }

    /**
     * @test
     */
    public function itShouldReturnEnvironmentsForGiveParts()
    {
        $environments = [
            [VersionedEnvironment::KEY => 1],
        ];
        $result = $this->builder->getEnvironments($environments);
        $this->assertContainsOnlyInstancesOf(Environment::class, $result);
    }
}
