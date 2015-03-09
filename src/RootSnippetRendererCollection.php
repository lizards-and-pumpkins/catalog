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
     * @param ProjectionSourceData $dataObject
     * @param ContextSource $contextSource
     * @return SnippetResultList
     */
    public function render(ProjectionSourceData $dataObject, ContextSource $contextSource)
    {
        if (!($dataObject instanceof RootSnippetSourceList)) {
            throw new InvalidProjectionDataSourceTypeException(
                'First argument must be instance of RootSnippetSourceList.'
            );
        }

        foreach ($this->renderers as $renderer) {
            $this->snippetResultList->merge($renderer->render($dataObject, $contextSource));
        }

        return $this->snippetResultList;
    }
}
