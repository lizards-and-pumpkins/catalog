<?php


namespace Brera\Environment;

require_once __DIR__ . '/EnvironmentDecoratorTestAbstract.php';

/**
 * @covers \Brera\Environment\WebsiteEnvironmentDecorator
 * @covers \Brera\Environment\EnvironmentDecorator
 */
class WebsiteEnvironmentDecoratorTest extends EnvironmentDecoratorTestAbstract
{
    /**
     * @return string
     */
    protected function getDecoratorUnderTestCode()
    {
        return WebsiteEnvironmentDecorator::CODE;
    }

    /**
     * @return array
     */
    protected function getStubEnvironmentData()
    {
        return [$this->getDecoratorUnderTestCode() => 'test-website-code'];
    }
    
    /**
     * @param Environment $stubEnvironment
     * @param array $stubEnvironmentData
     * @return WebsiteEnvironmentDecorator
     */
    protected function createEnvironmentDecoratorUnderTest(Environment $stubEnvironment, array $stubEnvironmentData)
    {
        return new WebsiteEnvironmentDecorator($stubEnvironment, $stubEnvironmentData);
    }
}
