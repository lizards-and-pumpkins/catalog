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

	/**
	 * @var string
	 */
	private $themeDirectory = 'foo';

	public function setUp()
	{
		$mockDataVersion = $this->getMockBuilder(DataVersion::class)
			->disableOriginalConstructor()
			->getMock();
		$this->environment = new VersionedEnvironment($mockDataVersion, $this->themeDirectory);
	}

	/**
	 * @class
	 */
	public function itShouldBeAnEnvironment()
	{
		$this->assertInstanceOf(Environment::class, $this->environment);
	}

	/**
	 * @test
	 */
	public function itShouldHaveAVersion()
	{
		$this->assertInstanceOf(DataVersion::class,	$this->environment->getVersion());
	}

	/**
	 * @test
	 */
	public function itShouldReturnThemeDirectory()
	{
	    $this->assertEquals($this->themeDirectory, $this->environment->getThemeDirectory());
	}
}
