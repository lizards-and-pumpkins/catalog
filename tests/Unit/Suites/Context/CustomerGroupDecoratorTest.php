<?php


namespace LizardsAndPumpkins\Context;

/**
 * @covers \LizardsAndPumpkins\Context\CustomerGroupContextDecorator
 * @covers \LizardsAndPumpkins\Context\ContextDecorator
 * @uses   \LizardsAndPumpkins\Context\ContextBuilder
 * @uses   \LizardsAndPumpkins\Context\VersionedContext
 * @uses   \LizardsAndPumpkins\DataVersion
 */
class CustomerGroupContextDecoratorTest extends AbstractContextDecoratorTest
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
