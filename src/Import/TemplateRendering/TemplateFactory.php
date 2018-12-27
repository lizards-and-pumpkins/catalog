<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\TemplateRendering;

interface TemplateFactory
{
    public function createTemplate($path): Template;
}
