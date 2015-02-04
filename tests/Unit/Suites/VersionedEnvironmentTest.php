<?php

namespace Brera;

/**
 * @covers \Brera\VersionedEnvironment
 */
class VersionedEnvironmentTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @var string
	 */
	private $testVersionValue = '1';
	
	/**
	 * @var VersionedEnvironment
	 */
	private $versionedEnvironment;

	/**
	 * @var DataVersion|\PHPUnit_Framework_MockObject_MockObject
	 */
	private $stubDataVersion;

	public function setUp()
	{
		$this->stubDataVersion = $this->getMockBuilder(DataVersion::class)
			->disableOriginalConstructor()
			->getMock();
		$this->stubDataVersion->expects($this->any())
			->method('__toString')
			->willReturn($this->testVersionValue);
		$this->versionedEnvironment = new VersionedEnvironment([VersionedEnvironment::CODE => $this->stubDataVersion]);
	}

	/**
	 * @test
	 */
	public function itShouldBeAnEnvironment()
	{
		$this->assertInstanceOf(Environment::class, $this->versionedEnvironment);
	}

	/**
	 * @test
	 * @expectedException \Brera\EnvironmentCodeNotFoundException
	 * @expectedExceptionMessage No value was not found in the current environment for the code 'foo'
	 */
	public function itShouldThrowAnExceptionWhenGettingTheValueWithANonmatchingCode()
	{
		$this->versionedEnvironment->getValue('foo');
	}

	/**
	 * @test
	 */
	public function itShouldReturnTheVersionForTheValue()
	{
		$result = $this->versionedEnvironment->getValue(VersionedEnvironment::CODE);
		$this->assertEquals($this->testVersionValue, $result);
	}

	/**
	 * @test
	 */
	public function itShouldAddTheVersionCodeToTheListOfSupportedCodes()
	{
		$result = $this->versionedEnvironment->getSupportedCodes();
		$this->assertInternalType('array', $result);
		$this->assertContains(VersionedEnvironment::CODE, $result);
	}

	/**
	 * @test
	 */
	public function itShouldReturnTheVersionIdentifier()
	{
		$this->assertEquals(VersionedEnvironment::CODE, $this->versionedEnvironment->getId());
	}
}
