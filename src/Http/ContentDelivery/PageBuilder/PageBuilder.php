<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Http\ContentDelivery\PageBuilder;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\HttpResponse;
use LizardsAndPumpkins\Import\PageMetaInfoSnippetContent;

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

    public function registerSnippetTransformation(string $snippetCode, callable $transformation);

    public function addSnippetToContainer(string $containerCode, string $snippetCode);

    public function addSnippetToPage(string $snippetCode, string $snippetContent);
}
