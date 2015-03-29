<?php

namespace Brera;

interface PageMetaInfoSnippetContent
{
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
