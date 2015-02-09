<?php


namespace Brera\Environment;

/**
 * @covers \Brera\Environment\LanguageEnvironmentDecorator
 * @covers \Brera\Environment\EnvironmentDecorator
 */
class LanguageEnvironmentDecoratorTest extends EnvironmentDecoratorTestAbstract
{
    /**
     * @return string
     */
    protected function getDecoratorUnderTestCode()
    {
        return LanguageEnvironmentDecorator::CODE;
    }

    /**
     * @return array
     */
    protected function getStubEnvironmentData()
    {
        return [$this->getDecoratorUnderTestCode() => 'test-language'];
    }
    
    /**
     * @param Environment $stubEnvironment
     * @param array $stubEnvironmentData
     * @return WebsiteEnvironmentDecorator
     */
    protected function createEnvironmentDecoratorUnderTest(Environment $stubEnvironment, array $stubEnvironmentData)
    {
        return new LanguageEnvironmentDecorator($stubEnvironment, $stubEnvironmentData);
    }
}
