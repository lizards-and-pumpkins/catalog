<?php


namespace Brera\Context;

/**
 * @covers \Brera\Context\LanguageContextDecorator
 * @covers \Brera\Context\ContextDecorator
 */
class LanguageContextDecoratorTest extends ContextDecoratorTestAbstract
{
    /**
     * @return string
     */
    protected function getDecoratorUnderTestCode()
    {
        return LanguageContextDecorator::CODE;
    }

    /**
     * @return array
     */
    protected function getStubContextData()
    {
        return [$this->getDecoratorUnderTestCode() => 'test-language'];
    }
    
    /**
     * @param Context $stubContext
     * @param array $stubContextData
     * @return WebsiteContextDecorator
     */
    protected function createContextDecoratorUnderTest(Context $stubContext, array $stubContextData)
    {
        return new LanguageContextDecorator($stubContext, $stubContextData);
    }
}
