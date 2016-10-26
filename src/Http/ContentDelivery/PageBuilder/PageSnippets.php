<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\ContentDelivery\PageBuilder;

interface PageSnippets
{
    /**
     * @return string[]
     */
    public function getSnippetCodes() : array;

    public function hasSnippetCode(string $snippetCode) : bool;

    public function getSnippetByCode(string $snippetCode) : string;
}
