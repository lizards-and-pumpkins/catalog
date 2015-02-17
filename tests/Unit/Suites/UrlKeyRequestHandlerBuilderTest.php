<?php


namespace Brera;

use Brera\Context\Context;
use Brera\Http\HttpUrl;
use Brera\DataPool\DataPoolReader;

class UrlKeyRequestHandlerBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UrlKeyRequestHandlerBuilder
     */
    private $builder;

    public function setUp()
    {
        $stubUrlPathKeyGenerator = $this->getMockBuilder(UrlPathKeyGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubDataPoolReader =$this->getMockBuilder(DataPoolReader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->builder = new UrlKeyRequestHandlerBuilder($stubUrlPathKeyGenerator, $stubDataPoolReader);
    }

    /**
     * @test
     */
    public function itShouldCreateAnUrlKeyRequestHandler()
    {
        $stubUrl = $this->getMockBuilder(HttpUrl::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubContext = $this->getMock(Context::class);
        $result = $this->builder->create($stubUrl, $stubContext);
        $this->assertInstanceOf(UrlKeyRequestHandler::class, $result);
    }
}
