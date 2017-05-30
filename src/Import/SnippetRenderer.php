<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\DataPool\KeyValueStore\Snippet;

interface SnippetRenderer
{
    /**
     * @param mixed $dataObject
     * @return Snippet[]
     */
    public function render($dataObject): array;
}
