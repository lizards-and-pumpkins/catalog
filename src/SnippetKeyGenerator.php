<?php

namespace Brera;

use Brera\Context\Context;

interface SnippetKeyGenerator
{
    /**
     * @param Context $context
     * @param array $data
     * @return string
     */
    public function getKeyForContext(Context $context, array $data = []);

    /**
     * @return string[]
     */
    public function getContextPartsUsedForKey();
}
