<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\TemplateRendering;

class TemplateFileFactory implements TemplateFactory
{
    public function createTemplate($path): Template
    {
        return new TemplateFile($path);
    }
}
