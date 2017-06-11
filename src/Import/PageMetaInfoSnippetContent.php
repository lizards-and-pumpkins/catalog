<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import;

interface PageMetaInfoSnippetContent
{
    const KEY_ROOT_SNIPPET_CODE = 'root_snippet_code';
    const KEY_PAGE_SNIPPET_CODES = 'page_snippet_codes';
    const KEY_CONTAINER_SNIPPETS = 'container_snippets';
    const URL_KEY = 'url_key';

    /**
     * @return mixed[]
     */
    public function getInfo(): array;

    public function getRootSnippetCode(): SnippetCode;

    /**
     * @return SnippetCode[]
     */
    public function getPageSnippetCodes(): array;

    /**
     * @return SnippetCode[]
     */
    public function getContainerSnippets(): array;
}
