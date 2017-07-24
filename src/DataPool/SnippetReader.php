<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\KeyValueStore\KeyValueStore;

class SnippetReader
{
    /**
     * @var KeyValueStore
     */
    private $keyValueStore;

    /**
     * @var string[]
     */
    private $contextParts;

    public function __construct(KeyValueStore $keyValueStore, string ...$contextParts)
    {
        $this->keyValueStore = $keyValueStore;
        $this->contextParts = $contextParts;
    }

    public function getPageMetaSnippet(string $urlKey, Context $context): string
    {
        $snippetKey = sprintf('meta_%s_%s', $urlKey, $context->getIdForParts(...$this->contextParts));

        return $this->getSnippet($snippetKey);
    }

    private function getSnippet(string $key): string
    {
        return (string) $this->keyValueStore->get($key);
    }
}
