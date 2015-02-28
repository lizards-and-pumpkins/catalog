<?php


namespace Brera;

use Brera\Context\Context;
use Brera\Http\HttpUrl;
use Brera\DataPool\DataPoolReader;
use Psr\Log\LoggerInterface;

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
        $stubSnippetKeyGeneratorLocator = $this->getMock(SnippetKeyGeneratorLocator::class);
        $stubDataPoolReader = $this->getMockBuilder(DataPoolReader::class)
            ->disableOriginalConstructor()
            ->getMock();
        $stubLogger = $this->getMock(LoggerInterface::class);
        $this->builder = new UrlKeyRequestHandlerBuilder(
            $stubUrlPathKeyGenerator,
            $stubSnippetKeyGeneratorLocator,
            $stubDataPoolReader,
            $stubLogger
        );
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
