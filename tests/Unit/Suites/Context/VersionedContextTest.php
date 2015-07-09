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
        $this->stubDataVersion = $this->getMock(DataVersion::class, [], [], '', false);
        $this->stubDataVersion->method('__toString')
            ->willReturn($this->testVersionValue);
        $this->versionedContext = new VersionedContext($this->stubDataVersion);
    }

    public function testContextInterfaceIsImplemented()
    {
        $this->assertInstanceOf(Context::class, $this->versionedContext);
    }

    public function testExceptionIsThrownIfNotMatchingCodeIsPassed()
    {
        $contextCode = 'foo';
        $this->setExpectedException(
            ContextCodeNotFoundException::class,
            sprintf('No value found in the current context for the code \'%s\'', $contextCode)
        );
        $this->versionedContext->getValue($contextCode);
    }

    public function testVersionIsReturnedAsValue()
    {
        $result = $this->versionedContext->getValue(VersionedContext::CODE);
        $this->assertEquals($this->testVersionValue, $result);
    }

    public function testVersionCodeIsAddedToListOfSupportedCodes()
    {
        $result = $this->versionedContext->getSupportedCodes();
        $this->assertInternalType('array', $result);
        $this->assertContains(VersionedContext::CODE, $result);
    }

    public function testVersionIdentifierIsReturned()
    {
        $expected = 'v:' . $this->testVersionValue;
        $this->assertEquals($expected, $this->versionedContext->getId());
    }

    public function testVersionIsIncludedInIdentifierWhenRequested()
    {
        $expected = 'v:' . $this->testVersionValue;
        $this->assertEquals($expected, $this->versionedContext->getIdForParts([VersionedContext::CODE]));
    }

    public function testVersionCodeIsSupported()
    {
        $this->assertTrue($this->versionedContext->supportsCode(VersionedContext::CODE));
    }

    public function testCodesOtherThenVersionAreNotSupported()
    {
        $this->assertFalse($this->versionedContext->supportsCode('foo'));
    }
}
