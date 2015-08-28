<?php


namespace Brera\Context;

/**
 * @covers \Brera\Context\CustomerGroupContextDecorator
 * @covers \Brera\Context\ContextDecorator
 * @uses   \Brera\Context\ContextBuilder
 * @uses   \Brera\Context\VersionedContext
 * @uses   \Brera\DataVersion
 */
class CustomerGroupContextDecoratorTest extends ContextDecoratorTestAbstract
{
    /**
     * @return string
     */
    protected function getDecoratorUnderTestCode()
    {
        return 'customer_group';
    }

    /**
     * @return string[]
     */
    protected function getStubContextData()
    {
        return [$this->getDecoratorUnderTestCode() => 'test-customer-group-code'];
    }

    /**
     * @param Context $stubContext
     * @param string[] $stubContextData
     * @return CustomerGroupContextDecorator
     */
    protected function createContextDecoratorUnderTest(Context $stubContext, array $stubContextData)
    {
        return new CustomerGroupContextDecorator($stubContext, $stubContextData);
    }

    public function testExceptionIsThrownIfValueIsNotFoundInSourceData()
    {
        $this->setExpectedExceptionRegExp(
            ContextCodeNotFoundException::class,
            '/No value found in the context source data for the code "[^\"]+"/'
        );
        $decorator = $this->createContextDecoratorUnderTest($this->getMockDecoratedContext(), []);
        $decorator->getValue($this->getDecoratorUnderTestCode());
    }
}
