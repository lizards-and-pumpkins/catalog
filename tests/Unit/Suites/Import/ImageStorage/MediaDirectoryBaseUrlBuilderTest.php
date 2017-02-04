<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ImageStorage;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrlBuilder;
use LizardsAndPumpkins\Context\BaseUrl\HttpBaseUrl;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Import\ImageStorage\Exception\InvalidMediaBaseUrlPathException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\ImageStorage\MediaDirectoryBaseUrlBuilder
 * @uses   \LizardsAndPumpkins\Context\BaseUrl\HttpBaseUrl
 */
class MediaDirectoryBaseUrlBuilderTest extends TestCase
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
        $this->mockBaseUrlBuilder = $this->createMock(BaseUrlBuilder::class);
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
        $this->expectException(\TypeError::class);
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
        $stubContext = $this->createMock(Context::class);
        $this->mockBaseUrlBuilder->method('create')->with($stubContext)->willReturn(
            new HttpBaseUrl('http://example.com/test/')
        );
        $result = $this->mediaBaseUrlBuilder->create($stubContext);
        $this->assertSame('http://example.com/test/' . $this->testMediaBaseUrlPath, (string) $result);
    }
}
