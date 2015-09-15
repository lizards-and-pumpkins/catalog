<?php

namespace LizardsAndPumpkins;

interface PageMetaInfoSnippetContent
{
    const KEY_ROOT_SNIPPET_CODE = 'root_snippet_code';
    const KEY_PAGE_SNIPPET_CODES = 'page_snippet_codes';
    const URL_KEY = 'url_key';
    
    /**
     * @return mixed[]
     */
    public function getInfo();

    /**
     * @return string
     */
    public function getRootSnippetCode();

    /**
     * @return string[]
     */
    public function getPageSnippetCodes();
}
