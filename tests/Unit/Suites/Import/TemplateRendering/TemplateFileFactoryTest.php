<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\TemplateRendering;

use PHPUnit\Framework\TestCase;

class TemplateFileFactoryTest extends TestCase
{
    /**
     * @var TemplateFileFactory
     */
    private $factory;

    protected function setUp()
    {
        $this->factory = new TemplateFileFactory();
    }

    public function testImplementsTemplateFactory()
    {
        $this->assertInstanceOf(TemplateFactory::class, $this->factory);
    }

    public function testCreatesTemplateFile()
    {
        $path = '/some/path';
        $this->assertInstanceOf(TemplateFile::class, $this->factory->createTemplate($path));
    }

    public function testPathIsReturned()
    {
        $path = '/some/path';
        $this->assertSame($path, (string)$this->factory->createTemplate($path));
        $this->assertSame($path, $this->factory->createTemplate($path)->getPath());
    }
}
