<?php

namespace LizardsAndPumpkins\DataPool\KeyGenerator;

use LizardsAndPumpkins\DataPool\KeyGenerator\SnippetKeyGenerator;

interface SnippetKeyGeneratorLocator
{
    /**
     * @param string $snippetCode
     * @return bool
     */
    public function canHandle($snippetCode);

    /**
     * @param string $snippetCode
     * @return SnippetKeyGenerator
     */
    public function getKeyGeneratorForSnippetCode($snippetCode);
}
