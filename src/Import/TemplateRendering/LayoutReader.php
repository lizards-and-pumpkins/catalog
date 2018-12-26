<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\TemplateRendering;

interface LayoutReader
{
    public function loadLayout(string $layoutXmlFilePath): Layout;
}
