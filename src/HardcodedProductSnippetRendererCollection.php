<?php

namespace Brera\PoC;

class HardcodedProductSnippetRendererCollection extends ProductSnippetRendererCollection
{
    /**
     * @var SnippetResultList
     */
    private $snippetResultList;
    
    /**
     * @var SnippetRenderer[]
     */
    private $renderers;

    /**
     * @param array $renderer
     * @param SnippetResultList $snippetResultList
     */
    public function __construct(
        array $renderer,
        SnippetResultList $snippetResultList
    ) {
        $this->snippetResultList = $snippetResultList;
        $this->renderers = $renderer;
    }

    /**
     * @return SnippetResultList
     */
    protected function getSnippetResultList()
    {
        return $this->snippetResultList;
    }

    /**
     * @return SnippetRenderer[]
     */
    protected function getSnippetRenderers()
    {
        return $this->renderers;
    }
}
