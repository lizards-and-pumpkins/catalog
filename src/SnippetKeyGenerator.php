<?php

namespace Brera;

use Brera\Context\Context;

interface SnippetKeyGenerator
{
    /**
     * @param mixed $identifier
     * @param Context $context
     * @return string
     */
    public function getKeyForContext($identifier, Context $context);

    /**
     * @return string[]
     */
    public function getContextPartsUsedForKey();
}
