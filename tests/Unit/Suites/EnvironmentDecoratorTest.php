<?php


namespace Brera;

/**
 * @covers   \Brera\EnvironmentDecorator
 * @covers   \Brera\TestConcreteEnvironmentDecorator
 */
class EnvironmentDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $testDecoratedComponentCode = 'component-code';
    
    /**
     * @var string
     */
    private $testSourceData = [TestConcreteEnvironmentDecorator::CODE => 'test-value'];

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Environment
     */
    private $mockEnvironment;

    /**
     * @var EnvironmentDecorator
     */
    private $decorator;

    public function setUp()
    {
        $this->mockEnvironment = $this->getMock(Environment::class);
        $this->mockEnvironment->expects($this->any())
            ->method('getSupportedCodes')
            ->willReturn([$this->testDecoratedComponentCode]);
        $this->decorator = new TestConcreteEnvironmentDecorator(
            $this->mockEnvironment, $this->testSourceData
        );
    }

    /**
     * @test
     */
    public function itShouldBeAnEnvironment()
    {
        $this->assertInstanceOf(Environment::class, $this->decorator);
    }

    /**
     * @test
     * @expectedException \Brera\EnvironmentCodeNotFoundException
     * @expectedExceptionMessage No value found in the environment source data for the code
     */
    public function itShouldThrowAnExceptionIfTheValueIsNotFoundInTheSourceData()
    {
        $decorator = new TestConcreteEnvironmentDecorator(
            $this->mockEnvironment, []
        );
        $decorator->getValue(TestConcreteEnvironmentDecorator::CODE);
    }

    /**
     * @test
     */
    public function itShouldReturnTheValueFromTheEnvironment()
    {
        $this->assertSame(
            $this->testSourceData[TestConcreteEnvironmentDecorator::CODE], $this->decorator->getValue(TestConcreteEnvironmentDecorator::CODE)
        );
    }

    /**
     * @test
     */
    public function itShouldDelegateToComponentIfCodeDoesNotMatch()
    {
        $this->mockEnvironment->expects($this->once())
            ->method('getValue')
            ->with($this->testDecoratedComponentCode);
        $this->decorator->getValue($this->testDecoratedComponentCode);
    }

    /**
     * @test
     */
    public function itShouldDelegateToComponentToFetchSupportedCodes()
    {
        $mockEnvironment = $this->getMock(Environment::class);
        $mockEnvironment->expects($this->once())
            ->method('getSupportedCodes')
            ->willReturn([$this->testDecoratedComponentCode]);
        $decorator = new TestConcreteEnvironmentDecorator(
            $mockEnvironment, [TestConcreteEnvironmentDecorator::CODE => $this->testSourceData]
        );
        $decorator->getSupportedCodes();
    }

    /**
     * @test
     */
    public function itShouldAddItsCodeToTheSupportedCodesArray()
    {
        $this->assertContains(TestConcreteEnvironmentDecorator::CODE, $this->decorator->getSupportedCodes());
    }

    /**
     * @test
     */
    public function itShouldReturnTheCodeAsTheId()
    {
        $this->assertEquals(TestConcreteEnvironmentDecorator::CODE, $this->decorator->getId());
    }

    /**
     * @test
     */
    public function itShouldReturnTheSourceData()
    {
        $method = new \ReflectionMethod($this->decorator, 'getSourceData');
        $method->setAccessible(true);
        $result = $method->invoke($this->decorator);
        $this->assertEquals($this->testSourceData, $result);
    }
}

class TestConcreteEnvironmentDecorator extends EnvironmentDecorator
{
    const CODE = 'test_code';

    /**
     * @return string
     */
    protected function getCode()
    {
        return self::CODE;
    }
}
