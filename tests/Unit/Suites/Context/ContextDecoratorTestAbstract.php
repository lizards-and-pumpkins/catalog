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
        $this->assertSame(
            $stubContextData[$code],
            $this->getDecoratorUnderTest()->getValue($code)
        );
    }

    /**
     * @param Context|\PHPUnit_Framework_MockObject_MockObject $stubContext
     * @param array $stubContextData
     * @return ContextDecorator
     */
    abstract protected function createContextDecoratorUnderTest(
        Context $stubContext,
        array $stubContextData
    );


    /**
     * @return mixed[]
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

    public function setUp()
    {
        $this->mockDecoratedContext = $this->getMock(Context::class);
        $this->mockDecoratedContext->expects($this->any())
            ->method('getSupportedCodes')
            ->willReturn([$this->decoratedComponentCode]);
        $this->decorator = $this->createContextDecoratorUnderTest(
            $this->mockDecoratedContext,
            $this->getStubContextData()
        );
    }

    /**
     * @test
     */
    final public function itShouldBeAnContext()
    {
        $this->assertInstanceOf(Context::class, $this->getDecoratorUnderTest());
    }

    /**
     * @test
     * @expectedException \Brera\Context\ContextCodeNotFoundException
     * @expectedExceptionMessage No value found in the context source data for the code
     */
    final public function itShouldThrowAnExceptionIfTheValueIsNotFoundInTheSourceData()
    {
        $decorator = $this->createContextDecoratorUnderTest($this->getMockDecoratedContext(), []);
        $decorator->getValue($this->getDecoratorUnderTestCode());
    }


    /**
     * @test
     */
    final public function itShouldDelegateToComponentIfCodeDoesNotMatch()
    {
        $this->getMockDecoratedContext()->expects($this->once())
            ->method('getValue')
            ->with($this->decoratedComponentCode);
        $this->getDecoratorUnderTest()->getValue($this->decoratedComponentCode);
    }

    /**
     * @test
     */
    final public function itShouldDelegateToComponentToFetchSupportedCodes()
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $mockDecoratedContext */
        $mockDecoratedContext = $this->getMock(Context::class);
        $mockDecoratedContext->expects($this->once())
            ->method('getSupportedCodes')
            ->willReturn([$this->decoratedComponentCode]);
        $decorator = $this->createContextDecoratorUnderTest(
            $mockDecoratedContext,
            $this->getStubContextData()
        );

        $decorator->getSupportedCodes();
    }

    /**
     * @test
     */
    final public function itShouldAddItsCodeToTheSupportedCodesArray()
    {
        $this->assertContains(
            $this->getDecoratorUnderTestCode(),
            $this->getDecoratorUnderTest()->getSupportedCodes()
        );
    }

    /**
     * @test
     */
    final public function itShouldReturnTheSourceData()
    {
        $method = new \ReflectionMethod($this->decorator, 'getSourceData');
        $method->setAccessible(true);
        $result = $method->invoke($this->decorator);
        $this->assertEquals($this->getStubContextData(), $result);
    }

    /**
     * @test
     */
    public function itShouldIncludeTheComponentIdInTheIdentifier()
    {
        $expected = $this->decoratedComponentCode . ':123';
        $this->mockDecoratedContext->expects($this->once())
            ->method('getId')
            ->willReturn($expected);
        $this->assertContains($expected, $this->getDecoratorUnderTest()->getId());
    }

    /**
     * @test
     */
    public function itShouldReturnAnIdentifierContainingTheCodeAndValue()
    {
        $code = $this->getDecoratorUnderTestCode();
        $expected = $code . ':' . $this->getDecoratorUnderTest()->getValue($code);
        $this->assertContains($expected, $this->getDecoratorUnderTest()->getId());
    }

    /**
     * @test
     */
    public function itShouldNotIncludeTheDecoratorIdInThePartialIdIfNotRequested()
    {
        $code = $this->getDecoratorUnderTestCode();
        $expected = $code . ':' . $this->getDecoratorUnderTest()->getValue($code);
        $this->assertNotContains($expected, $this->getDecoratorUnderTest()->getIdForParts([]));
    }

    /**
     * @test
     */
    public function itShouldIncludeTheDecoratorIdInThePartialIdIfRequested()
    {
        $code = $this->getDecoratorUnderTestCode();
        $expected = $code . ':' . $this->getDecoratorUnderTest()->getValue($code);
        $result = $this->getDecoratorUnderTest()->getIdForParts([$this->getDecoratorUnderTestCode()]);
        $this->assertContains($expected, $result);
    }

    /**
     * @test
     */
    public function itShouldDelegateToTheComponentToBuildThePartialId()
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

    /**
     * @test
     */
    public function itShouldReturnTheValueFromTheContext()
    {
        $this->defaultGetValueImplementationTest();
    }

    /**
     * @test
     */
    public function itShouldSupportTheDecoratorCode()
    {
        $code = $this->getDecoratorUnderTestCode();
        $this->assertTrue($this->getDecoratorUnderTest()->supportsCode($code));
    }

    /**
     * @test
     */
    public function itShouldDelegateToComponentIfCodeNotSupported()
    {
        $code = 'dummy-part';
        $this->mockDecoratedContext->expects($this->once())
            ->method('supportsCode')
            ->with($code);
        $this->getDecoratorUnderTest()->supportsCode($code);
    }

    /**
     * @test
     */
    public function itShouldReturnAContextStateInstance()
    {
        $this->mockDecoratedContext->expects($this->any())
            ->method('getValue')
            ->with(VersionedContext::CODE)
            ->willReturn('222');

        $this->assertInstanceOf(ContextState::class, $this->getDecoratorUnderTest()->getState());
    }

    /**
     * @test
     */
    public function itShouldReturnAContextInstance()
    {
        $mockContextState = $this->getMock(InternalContextState::class, [], [], '', false);
        $mockContextState->expects($this->any())->method('getVersion')->willReturn('123');
        $mockContextState->expects($this->any())->method('getContextDataSet')->willReturn([]);
        $class = get_class($this->getDecoratorUnderTest());
        $result = call_user_func($class . '::fromMemento', $mockContextState);
        $this->assertInstanceOf(Context::class, $result);
    }
}
