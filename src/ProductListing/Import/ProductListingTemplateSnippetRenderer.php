<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\Import\TemplateRendering\TemplateSnippetRenderer;
use LizardsAndPumpkins\ProductListing\Import\TemplateRendering\TemplateProjectionData;

class ProductListingTemplateSnippetRenderer implements SnippetRenderer
{
    const CODE = 'product_listing';

    /**
     * @var TemplateSnippetRenderer
     */
    private $templateSnippetRenderer;

    public function __construct(TemplateSnippetRenderer $templateSnippetRenderer) {
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
