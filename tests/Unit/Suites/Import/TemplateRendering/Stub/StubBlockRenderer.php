<?php

namespace LizardsAndPumpkins\Import\TemplateRendering\Stub;

use LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer;

class StubBlockRenderer extends BlockRenderer
{
    const LAYOUT_HANDLE = 'test-handle';
    
    /**
     * @return string
     */
    final public function getLayoutHandle()
    {
        return self::LAYOUT_HANDLE;
    }
}
