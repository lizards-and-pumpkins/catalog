<?php


namespace Brera\Environment;

abstract class EnvironmentDecoratorTestAbstract extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Environment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDecoratedEnvironment;

    /**
     * @var EnvironmentDecorator
     */
    private $decorator;

    /**
     * @var string
     */
    private $decoratedComponentCode = 'decorator-under-test-stub-code';

    public function setUp()
    {
        $this->mockDecoratedEnvironment = $this->getMock(Environment::class);
        $this->mockDecoratedEnvironment->expects($this->any())
            ->method('getSupportedCodes')
            ->willReturn([$this->decoratedComponentCode]);
        $this->decorator = $this->createEnvironmentDecoratorUnderTest(
            $this->mockDecoratedEnvironment,
            $this->getStubEnvironmentData()
        );
    }

    /**
     * @param Environment|\PHPUnit_Framework_MockObject_MockObject $stubEnvironment
     * @param array $stubEnvironmentData
     * @return EnvironmentDecorator
     */
    abstract protected function createEnvironmentDecoratorUnderTest(
        Environment $stubEnvironment,
        array $stubEnvironmentData
    );


    /**
     * @return array
     */
    abstract protected function getStubEnvironmentData();

    /**
     * @return string
     */
    abstract protected function getDecoratorUnderTestCode();

    /**
     * @return Environment|\PHPUnit_Framework_MockObject_MockObject
     */
    final protected function getMockDecoratedEnvironment()
    {
        return $this->mockDecoratedEnvironment;
    }

    /**
     * @return EnvironmentDecorator
     */
    final protected function getDecoratorUnderTest()
    {
        return $this->decorator;
    }

    /**
     * @test
     */
    final public function itShouldBeAnEnvironment()
    {
        $this->assertInstanceOf(Environment::class, $this->getDecoratorUnderTest());
    }

    /**
     * @test
     * @expectedException \Brera\Environment\EnvironmentCodeNotFoundException
     * @expectedExceptionMessage No value found in the environment source data for the code
     */
    final public function itShouldThrowAnExceptionIfTheValueIsNotFoundInTheSourceData()
    {
        $decorator = $this->createEnvironmentDecoratorUnderTest($this->getMockDecoratedEnvironment(), []);
        $decorator->getValue($this->getDecoratorUnderTestCode());
    }

    /**
     * @test
     */
    final public function itShouldDelegateToComponentIfCodeDoesNotMatch()
    {
        $this->getMockDecoratedEnvironment()->expects($this->once())
            ->method('getValue')
            ->with($this->decoratedComponentCode);
        $this->getDecoratorUnderTest()->getValue($this->decoratedComponentCode);
    }


    /**
     * @test
     */
    final public function itShouldDelegateToComponentToFetchSupportedCodes()
    {
        /** @var Environment|\PHPUnit_Framework_MockObject_MockObject $mockDecoratedEnvironment */
        $mockDecoratedEnvironment = $this->getMock(Environment::class);
        $mockDecoratedEnvironment->expects($this->once())
            ->method('getSupportedCodes')
            ->willReturn([$this->decoratedComponentCode]);
        $decorator = $this->createEnvironmentDecoratorUnderTest(
            $mockDecoratedEnvironment,
            $this->getStubEnvironmentData()
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
        $this->assertEquals($this->getStubEnvironmentData(), $result);
    }

    /**
     * @test
     */
    public function itShouldIncludeTheComponentIdInTheIdentifier()
    {
        $expected = $this->decoratedComponentCode . '123';
        $this->mockDecoratedEnvironment->expects($this->once())
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
        $expected = $code . $this->getDecoratorUnderTest()->getValue($code);
        $this->assertContains($expected, $this->getDecoratorUnderTest()->getId());
    }
    
    /**
     * @test
     */
    public function itShouldReturnTheValueFromTheEnvironment()
    {
        $this->defaultGetValueImplementationTest();
    }

    private function defaultGetValueImplementationTest()
    {
        $code = $this->getDecoratorUnderTestCode();
        $stubEnvironmentData = $this->getStubEnvironmentData();
        $this->assertSame(
            $stubEnvironmentData[$code],
            $this->getDecoratorUnderTest()->getValue($code)
        );
    }
}
