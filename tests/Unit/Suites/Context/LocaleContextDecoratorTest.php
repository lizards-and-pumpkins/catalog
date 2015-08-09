<?php


namespace Brera\Context;

/**
 * @covers \Brera\Context\LocaleContextDecorator
 * @covers \Brera\Context\ContextDecorator
 * @uses   \Brera\Context\ContextBuilder
 * @uses   \Brera\Context\VersionedContext
 * @uses   \Brera\DataVersion
 */
class LocaleContextDecoratorTest extends ContextDecoratorTestAbstract
{
    /**
     * @return string
     */
    protected function getDecoratorUnderTestCode()
    {
        return 'locale';
    }

    /**
     * @return mixed[]
     */
    protected function getStubContextData()
    {
        return [$this->getDecoratorUnderTestCode() => 'test-locale'];
    }

    /**
     * @param Context $stubContext
     * @param mixed[] $stubContextData
     * @return LocaleContextDecorator
     */
    protected function createContextDecoratorUnderTest(Context $stubContext, array $stubContextData)
    {
        return new LocaleContextDecorator($stubContext, $stubContextData);
    }
}
