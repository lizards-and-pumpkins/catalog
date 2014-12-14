<?php

namespace Brera\PoC;

class EnvironmentBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EnvironmentBuilder
     */
    private $builder;

    public function setUp()
    {
        $this->builder = new EnvironmentBuilder(DataVersion::fromVersionString('1'));
    }

    /**
     * @test
     */
    public function itShouldReturnAnEnvironment()
    {
        $dummyXml = '<data></data>';
        $result = $this->builder->createEnvironmentFromXml($dummyXml);
        $this->assertInstanceOf(Environment::class, $result);
    }
}
