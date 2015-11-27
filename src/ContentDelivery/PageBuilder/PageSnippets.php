<?php
namespace LizardsAndPumpkins\ContentDelivery\PageBuilder;

interface PageSnippets
{
    /**
     * @return string[]
     */
    public function getSnippetCodes();

    /**
     * @param string $snippetCode
     * @return bool
     */
    public function hasSnippetCode($snippetCode);

    /**
     * @param string $snippetCode
     * @return string
     */
    public function getSnippetByCode($snippetCode);

    /**
     * @param string $snippetCode
     * @param string $content
     */
    public function updateSnippetByCode($snippetCode, $content);
}
