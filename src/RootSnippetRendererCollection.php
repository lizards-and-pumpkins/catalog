<?php

namespace Brera;

use Brera\Context\ContextSource;

class RootSnippetRendererCollection implements SnippetRendererCollection
{
    /**
     * @var SnippetResultList
     */
    private $snippetResultList;

    /**
     * @var SnippetRenderer[]
     */
    private $renderers = [];

    /**
     * @param SnippetRenderer[] $renderers
     * @param SnippetResultList $snippetResultList
     */
    public function __construct(array $renderers, SnippetResultList $snippetResultList)
    {
        $this->renderers = $renderers;
        $this->snippetResultList = $snippetResultList;
    }

    /**
     * @param ProjectionSourceData $rootSnippetSourceList
     * @param ContextSource $contextSource
     * @return SnippetResultList
     */
    public function render(ProjectionSourceData $rootSnippetSourceList, ContextSource $contextSource)
    {
        if (!($rootSnippetSourceList instanceof RootSnippetSourceList)) {
            throw new InvalidProjectionDataSourceTypeException(
                'First argument must be instance of RootSnippetSourceList.'
            );
        }

        foreach ($this->renderers as $renderer) {
            $this->snippetResultList->merge($renderer->render($rootSnippetSourceList, $contextSource));
        }

        return $this->snippetResultList;
    }
}
