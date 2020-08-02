<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Import\TemplateRendering;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEvent;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\TemplateRendering\TemplateProjectionData
 * @uses   \LizardsAndPumpkins\Context\DataVersion\DataVersion
 * @uses   \LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEvent
 */
class TemplateProjectionDataTest extends TestCase
{
    public function testReturnsTheInjectedTemplateContent(): void
    {
        $testContent = 'foo';
        $dataVersion = DataVersion::fromVersionString('bar');
        $this->assertSame($testContent, (new TemplateProjectionData($testContent, $dataVersion))->getContent());
    }

    public function testReturnsTheInjectedDataVersion(): void
    {
        $testContent = 'foo';
        $dataVersion = DataVersion::fromVersionString('bar');
        $templateProjectionData = new TemplateProjectionData($testContent, $dataVersion);
        $this->assertEquals((string) $dataVersion, $templateProjectionData->getDataVersion());
    }

    public function testCanBeCreatedFromTemplateWasUpdatedDomanEvent(): void
    {
        $event = new TemplateWasUpdatedDomainEvent(
            $templateId = 'foo',
            $templateContent = 'bar',
            $dataVersion = DataVersion::fromVersionString('baz')
        );
        $projectionData = TemplateProjectionData::fromEvent($event);
        $this->assertInstanceOf(TemplateProjectionData::class, $projectionData);
        $this->assertSame($event->getTemplateContent(), $projectionData->getContent());
        $this->assertSame($event->getDataVersion(), $projectionData->getDataVersion());
    }
}
