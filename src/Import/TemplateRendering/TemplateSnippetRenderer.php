<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\TemplateRendering;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextSource;
use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;
use LizardsAndPumpkins\Import\Exception\InvalidDataObjectTypeException;
use LizardsAndPumpkins\Import\SnippetRenderer;
use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;
use LizardsAndPumpkins\ProductListing\Import\TemplateRendering\TemplateProjectionData;

class TemplateSnippetRenderer implements SnippetRenderer
{
    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;

    /**
     * @var BlockRenderer
     */
    private $blockRenderer;

    /**
     * @var ContextSource
     */
    private $contextSource;

    public function __construct(
        SnippetKeyGenerator $snippetKeyGenerator,
        BlockRenderer $blockRenderer,
        ContextSource $contextSource
    ) {
        $this->snippetKeyGenerator = $snippetKeyGenerator;
        $this->blockRenderer = $blockRenderer;
        $this->contextSource = $contextSource;
    }

    /**
     * @param TemplateProjectionData $dataToRender
     * @return Snippet[]
     */
    public function render($dataToRender): array
    {
        if (! $dataToRender instanceof TemplateProjectionData) {
            throw new InvalidDataObjectTypeException(
                sprintf('Data object must be TemplateProjectionData, got %s.', typeof($dataToRender))
            );
        }

        return array_map(function (Context $context) use ($dataToRender) {
            return $this->renderTemplateSnippetForContext($dataToRender, $context);
        }, $this->contextSource->getAllAvailableContextsWithVersionApplied($dataToRender->getDataVersion()));
    }

    private function renderTemplateSnippetForContext(TemplateProjectionData $dataToRender, Context $context): Snippet
    {
        $content = $this->blockRenderer->render($dataToRender, $context);
        $key = $this->snippetKeyGenerator->getKeyForContext($context, []);

        return Snippet::create($key, $content);
    }
}
