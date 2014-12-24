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
     * @param array $renderers
     * @param SnippetResultList $snippetResultList
     */
    public function __construct(array $renderers, SnippetResultList $snippetResultList)
    {
        $this->renderers = $renderers;
	    $this->snippetResultList = $snippetResultList;
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
