<?php

namespace Brera;

/**
 * @covers \Brera\VersionedEnvironment
 */
class VersionedEnvironmentTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var VersionedEnvironment
	 */
	private $environment;

	public function setUp()
	{
		$mockDataVersion = $this->getMockBuilder(DataVersion::class)
			->disableOriginalConstructor()
			->getMock();
		$this->environment = new VersionedEnvironment([VersionedEnvironment::CODE => $mockDataVersion]);
	}

	/**
	 * @test
	 */
	public function itShouldBeAnEnvironment()
	{
		$this->assertInstanceOf(Environment::class, $this->environment);
	}
}
