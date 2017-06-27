<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http\ContentDelivery\PageBuilder;

use LizardsAndPumpkins\Import\SnippetCode;

interface PageSnippets
{
    /**
     * @return SnippetCode[]
     */
    public function getSnippetCodes(): array;

    public function hasSnippetCode(SnippetCode $snippetCode): bool;

    public function getSnippetByCode(SnippetCode $snippetCode): string;
}
