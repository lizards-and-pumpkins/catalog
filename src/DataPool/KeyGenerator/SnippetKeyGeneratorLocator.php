<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\KeyGenerator;

interface SnippetKeyGeneratorLocator
{
    public function canHandle(string $snippetCode) : bool;

    public function getKeyGeneratorForSnippetCode(string $snippetCode) : SnippetKeyGenerator;
}
