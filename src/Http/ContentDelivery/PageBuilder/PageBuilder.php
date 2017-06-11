<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Http\ContentDelivery\PageBuilder;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;
use LizardsAndPumpkins\Import\SnippetCode;

interface PageBuilder
{
    /**
     * @param PageMetaInfoSnippetContent $metaInfo
     * @param Context $context
     * @param mixed[] $keyGeneratorParams
     * @return HttpResponse
     */
    public function buildPage(
        PageMetaInfoSnippetContent $metaInfo,
        Context $context,
        array $keyGeneratorParams
    ): HttpResponse;

    /**
     * @param string[] $snippetCodeToKeyMap
     * @param string[] $snippetKeyToContentMap
     */
    public function addSnippetsToPage(array $snippetCodeToKeyMap, array $snippetKeyToContentMap);

    public function registerSnippetTransformation(SnippetCode $snippetCode, callable $transformation);

    public function addSnippetToContainer(SnippetCode $containerCode, SnippetCode $snippetCode);

    public function addSnippetToPage(SnippetCode $snippetCode, string $snippetContent);
}
