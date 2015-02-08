<?php


namespace Brera;


class WebsiteEnvironmentDecoratorTestOff extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $stubEnvironmentData = ['website' => 'test-website-code'];
    
    /**
     * @var Environment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockDecoratedEnvironment;
    
    
    /**
     * @var WebsiteEnvironmentDecorator
     */
    private $decorator;

    public function setUp()
    {
        $this->mockDecoratedEnvironment = $this->getMock(Environment::class);
        $this->decorator = new WebsiteEnvironmentDecorator($this->mockDecoratedEnvironment, $this->stubEnvironmentData);
    }

    public function testNothing()
    {
        
    }
}
