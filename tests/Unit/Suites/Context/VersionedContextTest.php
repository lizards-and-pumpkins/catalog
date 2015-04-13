<?php

namespace Brera\Context;

use Brera\DataVersion;

/**
 * @covers \Brera\Context\VersionedContext
 * @uses   \Brera\Context\ContextBuilder
 * @uses   \Brera\DataVersion
 */
class VersionedContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $testVersionValue = '1';

    /**
     * @var VersionedContext
     */
    private $versionedContext;

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
        $this->versionedContext = new VersionedContext($this->stubDataVersion);
    }

    /**
     * @test
     */
    public function itShouldBeAnContext()
    {
        $this->assertInstanceOf(Context::class, $this->versionedContext);
    }

    /**
     * @test
     * @expectedException \Brera\Context\ContextCodeNotFoundException
     * @expectedExceptionMessage No value was not found in the current context for the code 'foo'
     */
    public function itShouldThrowAnExceptionWhenGettingTheValueWithANonMatchingCode()
    {
        $this->versionedContext->getValue('foo');
    }

    /**
     * @test
     */
    public function itShouldReturnTheVersionForTheValue()
    {
        $result = $this->versionedContext->getValue(VersionedContext::CODE);
        $this->assertEquals($this->testVersionValue, $result);
    }

    /**
     * @test
     */
    public function itShouldAddTheVersionCodeToTheListOfSupportedCodes()
    {
        $result = $this->versionedContext->getSupportedCodes();
        $this->assertInternalType('array', $result);
        $this->assertContains(VersionedContext::CODE, $result);
    }

    /**
     * @test
     */
    public function itShouldReturnTheVersionIdentifier()
    {
        $expected = 'v:' . $this->testVersionValue;
        $this->assertEquals($expected, $this->versionedContext->getId());
    }

    /**
     * @test
     */
    public function itShouldIncludeTheVersionInTheIdentifierWhenItIsRequested()
    {
        $expected = 'v:' . $this->testVersionValue;
        $this->assertEquals($expected, $this->versionedContext->getIdForParts([VersionedContext::CODE]));
    }

    /**
     * @test
     */
    public function itShouldSupportTheVersionCode()
    {
        $this->assertTrue($this->versionedContext->supportsCode(VersionedContext::CODE));
    }

    /**
     * @test
     */
    public function itShouldNotSupportCodesOtherThenVersion()
    {
        $this->assertFalse($this->versionedContext->supportsCode('foo'));
    }
}
