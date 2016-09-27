<?php

namespace LizardsAndPumpkins\Import\TemplateRendering\Stub;

use LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer;

class StubBlockRenderer extends BlockRenderer
{
    const LAYOUT_HANDLE = 'test-handle';
    
    final public function getLayoutHandle() : string
    {
        return self::LAYOUT_HANDLE;
    }
}
