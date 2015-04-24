<?php

namespace Brera;

use Brera\Context\ContextSource;

class RootSnippetRendererCollection implements SnippetRendererCollection
{
    /**
     * @var SnippetList
     */
    private $snippetList;

    /**
     * @var SnippetRenderer[]
     */
    private $renderers = [];

    /**
     * @param SnippetRenderer[] $renderers
     * @param SnippetList $snippetList
     */
    public function __construct(array $renderers, SnippetList $snippetList)
    {
        $this->renderers = $renderers;
        $this->snippetList = $snippetList;
    }

    /**
     * @param ProjectionSourceData $rootSnippetSourceList
     * @param ContextSource $contextSource
     * @return SnippetList
     */
    public function render(ProjectionSourceData $rootSnippetSourceList, ContextSource $contextSource)
    {
        if (!($rootSnippetSourceList instanceof RootSnippetSourceList)) {
            throw new InvalidProjectionDataSourceTypeException(
                'First argument must be instance of RootSnippetSourceList.'
            );
        }

        foreach ($this->renderers as $renderer) {
            $this->snippetList->merge($renderer->render($rootSnippetSourceList, $contextSource));
        }

        return $this->snippetList;
    }
}
