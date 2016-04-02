<?php

namespace LizardsAndPumpkins\Import\ImageStorage;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Context\BaseUrl\HttpBaseUrl;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\ImageStorage\Exception\InvalidMediaBaseUrlPathException;


/**
 * @covers \LizardsAndPumpkins\Import\ImageStorage\MediaDirectoryBaseUrlBuilder
 * @uses   \LizardsAndPumpkins\Context\BaseUrl\HttpBaseUrl
 */
class MediaDirectoryBaseUrlBuilderTest extends \PHPUnit_Framework_TestCase
{
    private $testMediaBaseUrlPath = 'test-media/';

    /**
     * @var BaseUrlBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockBaseUrlBuilder;

    /**
     * @var MediaDirectoryBaseUrlBuilder
     */
    private $mediaBaseUrlBuilder;

    protected function setUp()
    {
        $this->mockBaseUrlBuilder = $this->getMock(BaseUrlBuilder::class);
        $this->mediaBaseUrlBuilder = new MediaDirectoryBaseUrlBuilder(
            $this->mockBaseUrlBuilder,
            $this->testMediaBaseUrlPath
        );
    }
    
    public function testItImplementsTheMediaBaseUrlBuilder()
    {
        $this->assertInstanceOf(MediaBaseUrlBuilder::class, $this->mediaBaseUrlBuilder);
    }

    public function testItThrowsAnExceptionIfTheMediaBaseUrlPathIsNoString()
    {
        $this->expectException(InvalidMediaBaseUrlPathException::class);
        $this->expectExceptionMessage('The media base URL path has to be a string, got "integer"');
        $invalidPath = 123;
        new MediaDirectoryBaseUrlBuilder($this->mockBaseUrlBuilder, $invalidPath);
    }

    public function testItThrowsAnExceptionIfTheMediaBaseUrlPathDoesNotEndWithASlash()
    {
        $this->expectException(InvalidMediaBaseUrlPathException::class);
        $this->expectExceptionMessage('The media base URL path has to end with a training slash');
        $invalidPath = 'media/without/slash/at/the/end';
        new MediaDirectoryBaseUrlBuilder($this->mockBaseUrlBuilder, $invalidPath);
    }

    public function testItReturnsTheValueFromTheBaseUrlBuilderIncludingThePathSuffix()
    {
        $stubContext = $this->getMock(Context::class);
        $this->mockBaseUrlBuilder->method('create')->with($stubContext)->willReturn(
            HttpBaseUrl::fromString('http://example.com/test/')
        );
        $result = $this->mediaBaseUrlBuilder->create($stubContext);
        $this->assertSame('http://example.com/test/' . $this->testMediaBaseUrlPath, (string) $result);
    }
}
