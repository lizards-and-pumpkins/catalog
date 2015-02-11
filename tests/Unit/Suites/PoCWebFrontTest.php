<?php

namespace Brera;

use Brera\Environment\Environment;
use Brera\Http\HttpRequest;

/**
 * @covers \Brera\PoCWebFront
 * @covers \Brera\WebFront
 */
class PoCWebFrontTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PoCWebFront
     */
    private $pocWebFront;

    protected function setUp()
    {
        $stubHttpRequest = $this->getMockBuilder(HttpRequest::class)
        ->disableOriginalConstructor()
        ->getMock();
        $stubMasterFactory = $this->getMock(MasterFactory::class);
        $stubEnvironment = $this->getMock(Environment::class);

        $this->pocWebFront = new PoCWebFront($stubHttpRequest, $stubEnvironment, $stubMasterFactory);
    }

    /**
     * @test
     */
    public function itShouldReturnMasterFactory()
    {
        $result = $this->pocWebFront->getMasterFactory();
        $this->assertInstanceOf(MasterFactory::class, $result);
    }
}
