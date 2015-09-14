<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Context;

interface SnippetKeyGenerator
{
    /**
     * @param Context $context
     * @param mixed[] $data
     * @return string
     */
    public function getKeyForContext(Context $context, array $data);

    /**
     * @return string[]
     */
    public function getContextPartsUsedForKey();
}
