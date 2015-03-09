<?php

namespace Brera;

class RootSnippetSourceBuilder
{
    /**
     * @param string $xml
     * @return RootSnippetSource
     */
    public function createFromXml($xml)
    {
        return new RootSnippetSource();
    }
}
