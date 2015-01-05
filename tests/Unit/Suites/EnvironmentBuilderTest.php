<?php

namespace Brera;

class VersionedEnvironmentBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var VersionedEnvironmentBuilder
     */
    private $builder;

    public function setUp()
    {
        $version = DataVersion::fromVersionString('1');
        $this->builder = new VersionedEnvironmentBuilder($version);
    }

    /**
     * @test
     */
    public function itShouldBeAnEnvironmentBuilder()
    {
        $this->assertInstanceOf(EnvironmentBuilder::class, $this->builder);
    }

    /**
     * @test
     */
    public function itShouldReturnAVersionedEnvironment()
    {
        $dummyXml = '<data></data>';
        $result = $this->builder->createEnvironmentFromXml($dummyXml);
        $this->assertInstanceOf(VersionedEnvironment::class, $result);
    }
}
