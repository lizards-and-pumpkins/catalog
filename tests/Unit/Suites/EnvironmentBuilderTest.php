<?php

namespace Brera;

/**
 * @covers \Brera\VersionedEnvironmentBuilder
 * @uses \Brera\DataVersion
 * @uses \Brera\VersionedEnvironment
 */
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
		$dummyXml = '<root />';
		$result = $this->builder->createEnvironmentFromXml($dummyXml);
		$this->assertInstanceOf(VersionedEnvironment::class, $result);
	}
}
