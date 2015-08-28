<?php

namespace Brera\Context;

abstract class ContextDecoratorTestAbstract extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDecoratedContext;

    /**
     * @var ContextDecorator
     */
    private $decorator;

    /**
     * @var string
     */
    private $decoratedComponentCode = 'decorator-under-test-stub-code';

    private function defaultGetValueImplementationTest()
    {
        $code = $this->getDecoratorUnderTestCode();
        $stubContextData = $this->getStubContextData();
        $this->assertSame($stubContextData[$code], $this->getDecoratorUnderTest()->getValue($code));
    }

    /**
     * @param Context|\PHPUnit_Framework_MockObject_MockObject $stubContext
     * @param string[] $stubContextData
     * @return ContextDecorator
     */
    abstract protected function createContextDecoratorUnderTest(Context $stubContext, array $stubContextData);


    /**
     * @return string[]
     */
    abstract protected function getStubContextData();

    /**
     * @return string
     */
    abstract protected function getDecoratorUnderTestCode();

    /**
     * @return Context|\PHPUnit_Framework_MockObject_MockObject
     */
    final protected function getMockDecoratedContext()
    {
        return $this->mockDecoratedContext;
    }

    /**
     * @return ContextDecorator
     */
    final protected function getDecoratorUnderTest()
    {
        return $this->decorator;
    }

    protected function setUp()
    {
        $this->mockDecoratedContext = $this->getMock(Context::class);
        $this->mockDecoratedContext->method('getSupportedCodes')
            ->willReturn([$this->decoratedComponentCode]);
        $this->decorator = $this->createContextDecoratorUnderTest(
            $this->mockDecoratedContext,
            $this->getStubContextData()
        );
    }

    final public function testContextInterfaceIsImplemented()
    {
        $this->assertInstanceOf(Context::class, $this->getDecoratorUnderTest());
    }

    final public function testHandlingIsDelegatedToComponentIfCodeDoesNotMatch()
    {
        $this->getMockDecoratedContext()->expects($this->once())
            ->method('getValue')
            ->with($this->decoratedComponentCode);
        $this->getDecoratorUnderTest()->getValue($this->decoratedComponentCode);
    }

    final public function testHandlingIsDelegatedToComponentToFetchSupportedCodes()
    {
        $mockDecoratedContext = $this->getMock(Context::class);
        $mockDecoratedContext->expects($this->once())
            ->method('getSupportedCodes')
            ->willReturn([$this->decoratedComponentCode]);

        $decorator = $this->createContextDecoratorUnderTest($mockDecoratedContext, $this->getStubContextData());
        $decorator->getSupportedCodes();
    }

    final public function testCodeIsAddedToSupportedCodesArray()
    {
        $this->assertContains($this->getDecoratorUnderTestCode(), $this->getDecoratorUnderTest()->getSupportedCodes());
    }

    final public function testSourceDataIsReturned()
    {
        $method = new \ReflectionMethod($this->decorator, 'getSourceData');
        $method->setAccessible(true);
        $result = $method->invoke($this->decorator);
        $this->assertEquals($this->getStubContextData(), $result);
    }

    public function testComponentIdIsIncludedIntoIdentifier()
    {
        $expected = $this->decoratedComponentCode . ':123';
        $this->mockDecoratedContext->expects($this->once())
            ->method('getId')
            ->willReturn($expected);
        $this->assertContains($expected, $this->getDecoratorUnderTest()->getId());
    }

    public function testIdentifierContainingCodeAndValueIsReturned()
    {
        $code = $this->getDecoratorUnderTestCode();
        $expected = $code . ':' . $this->getDecoratorUnderTest()->getValue($code);
        $this->assertContains($expected, $this->getDecoratorUnderTest()->getId());
    }

    public function testDecoratorIdIsNotIncludedInPartialIdIfNotRequested()
    {
        $code = $this->getDecoratorUnderTestCode();
        $expected = $code . ':' . $this->getDecoratorUnderTest()->getValue($code);
        $this->assertNotContains($expected, $this->getDecoratorUnderTest()->getIdForParts([]));
    }

    public function testDecoratorIdIsIncludedInPartialIdIfRequested()
    {
        $code = $this->getDecoratorUnderTestCode();
        $expected = $code . ':' . $this->getDecoratorUnderTest()->getValue($code);
        $result = $this->getDecoratorUnderTest()->getIdForParts([$this->getDecoratorUnderTestCode()]);
        $this->assertContains($expected, $result);
    }

    public function testBuildingPartialIdIsDelegatedToComponent()
    {
        $code = $this->getDecoratorUnderTestCode();
        $decoratorId = $code . ':' . $this->getDecoratorUnderTest()->getValue($code);
        $componentId = 'foo:bar';

        $this->mockDecoratedContext->expects($this->once())->method('getIdForParts')
            ->willReturn('foo:bar');
        $result = $this->getDecoratorUnderTest()->getIdForParts([
            $this->getDecoratorUnderTestCode(),
            $this->decoratedComponentCode
        ]);
        $this->assertEquals($decoratorId . '_' . $componentId, $result);
    }

    public function testValueFromContextIsReturned()
    {
        $this->defaultGetValueImplementationTest();
    }

    public function testDecoratorCodeIsSupported()
    {
        $code = $this->getDecoratorUnderTestCode();
        $this->assertTrue($this->getDecoratorUnderTest()->supportsCode($code));
    }

    public function testHandlingIsDelegatedToComponentIfCodeIsNotSupported()
    {
        $code = 'dummy-part';
        $this->mockDecoratedContext->expects($this->once())
            ->method('supportsCode')
            ->with($code);
        $this->getDecoratorUnderTest()->supportsCode($code);
    }
}
