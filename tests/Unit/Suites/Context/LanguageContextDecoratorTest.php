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
        return 'language';
    }

    /**
     * @return mixed[]
     */
    protected function getStubContextData()
    {
        return [$this->getDecoratorUnderTestCode() => 'test-language'];
    }

    /**
     * @param Context $stubContext
     * @param mixed[] $stubContextData
     * @return LanguageContextDecorator
     */
    protected function createContextDecoratorUnderTest(Context $stubContext, array $stubContextData)
    {
        return new LanguageContextDecorator($stubContext, $stubContextData);
    }
}
