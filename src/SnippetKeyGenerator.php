<?php

namespace Brera;

use Brera\Context\Context;

interface SnippetKeyGenerator
{
    /**
     * @param string $snippetCode
     * @param mixed $identifier
     * @param Context $context
     * @return string
     */
    public function getKeyForContext($snippetCode, $identifier, Context $context);
}
