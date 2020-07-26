<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http;

use LizardsAndPumpkins\Context\Website\UrlToWebsiteMap;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Http\UrlToWebsiteMapBasedUrlParser
 */
class UrlToWebsiteMapBasedUrlParserTest extends TestCase
{
    public function testDelegatesPathExtractionToUrlToWebsiteMap(): void
    {
        $testPath = 'foo';

        $dummyHttpUrl = $this->createMock(HttpUrl::class);

        $stubUrlToWebsiteMap = $this->createMock(UrlToWebsiteMap::class);
        $stubUrlToWebsiteMap->method('getRequestPathWithoutWebsitePrefix')->willReturn($testPath);

        $parser = new UrlToWebsiteMapBasedUrlParser($stubUrlToWebsiteMap);

        $this->assertSame($testPath, $parser->getPath($dummyHttpUrl));
    }
}
