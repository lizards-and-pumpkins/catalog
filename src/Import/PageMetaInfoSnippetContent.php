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
    public function toArray(): array;

    public function getRootSnippetCode(): string;

    /**
     * @return string[]
     */
    public function getPageSnippetCodes(): array;

    /**
     * @return array[]
     */
    public function getContainerSnippets(): array;
}
