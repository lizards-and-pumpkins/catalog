<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ProductListing\Import\TemplateRendering;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\TemplateRendering\TemplateProjectionData
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 */
class TemplateProjectionDataTest extends TestCase
{
    public function testReturnsTheInjectedTemplateContent()
    {
        $testContent = 'foo';
        $dataVersion = DataVersion::fromVersionString('bar');
        $this->assertSame($testContent, (new TemplateProjectionData($testContent, $dataVersion))->getContent());
    }

    public function testReturnsTheInjectedDataVersion()
    {
        $testContent = 'foo';
        $dataVersion = DataVersion::fromVersionString('bar');
        $templateProjectionData = new TemplateProjectionData($testContent, $dataVersion);
        $this->assertEquals((string) $dataVersion, $templateProjectionData->getDataVersion());
    }
}
