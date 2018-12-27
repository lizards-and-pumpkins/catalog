<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\TemplateRendering;

use PHPUnit\Framework\TestCase;

class TemplateFileTest extends TestCase
{
    /**
     * @var TemplateFile
     */
    private $templateFile;

    private $testPath;

    protected function setUp()
    {
        $this->testPath     = '/some/path';
        $this->templateFile = new TemplateFile($this->testPath);
    }

    public function testImplementsTemplate()
    {
        $this->assertInstanceOf(Template::class, $this->templateFile);
    }

    public function testReturnsPath()
    {
        $this->assertSame($this->testPath, $this->templateFile->getPath());
        $this->assertSame($this->testPath, (string)$this->templateFile);
    }
}
