<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\KeyGenerator;

use LizardsAndPumpkins\Import\SnippetCode;

interface SnippetKeyGeneratorLocator
{
    public function canHandle(SnippetCode $snippetCode) : bool;

    public function getKeyGeneratorForSnippetCode(SnippetCode $snippetCode) : SnippetKeyGenerator;
}
