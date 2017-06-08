<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductDetail\Import;

use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\Import\TemplateRendering\TemplateProjectionData;
use LizardsAndPumpkins\Import\TemplateRendering\TemplateSnippetRenderer;

class ProductDetailTemplateSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_detail_view';

    /**
     * @var TemplateSnippetRenderer
     */
    private $templateSnippetRenderer;

    public function __construct(TemplateSnippetRenderer $templateSnippetRenderer)
    {
        $this->templateSnippetRenderer = $templateSnippetRenderer;
    }

    /**
     * @param TemplateProjectionData $dataToRender
     * @return Snippet[]
     */
    public function render($dataToRender): array
    {
        return $this->templateSnippetRenderer->render($dataToRender);
    }
}
