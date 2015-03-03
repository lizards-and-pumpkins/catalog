<?php


namespace Brera\Context;

/**
 * @covers \Brera\Context\CustomerGroupContextDecorator
 * @covers \Brera\Context\ContextDecorator
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
     * @return array
     */
    protected function getStubContextData()
    {
        return [$this->getDecoratorUnderTestCode() => 'test-customer-group-code'];
    }
    
    /**
     * @param Context $stubContext
     * @param array $stubContextData
     * @return CustomerGroupContextDecorator
     */
    protected function createContextDecoratorUnderTest(Context $stubContext, array $stubContextData)
    {
        return new CustomerGroupContextDecorator($stubContext, $stubContextData);
    }
}
