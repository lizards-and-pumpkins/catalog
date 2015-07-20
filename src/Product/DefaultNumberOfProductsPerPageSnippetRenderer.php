<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\RootSnippetSourceList;
use Brera\Snippet;
use Brera\SnippetKeyGenerator;
use Brera\SnippetList;
use Brera\SnippetRenderer;

class DefaultNumberOfProductsPerPageSnippetRenderer implements SnippetRenderer
{
    /**
     * @var SnippetList
     */
    private $snippetList;

    /**
     * @var SnippetKeyGenerator
     */
    private $snippetKeyGenerator;

    public function __construct(SnippetList $snippetList, SnippetKeyGenerator $snippetKeyGenerator)
    {
        $this->snippetList = $snippetList;
        $this->snippetKeyGenerator = $snippetKeyGenerator;
    }

    /**
     * @param RootSnippetSourceList $rootSnippetSourceList
     * @param ContextSource $contextSource
     * @return SnippetList
     */
    public function render(RootSnippetSourceList $rootSnippetSourceList, ContextSource $contextSource)
    {
        $contextParts = $this->snippetKeyGenerator->getContextPartsUsedForKey();
        $contexts = $contextSource->getContextsForParts($contextParts);
        foreach ($contexts as $context) {
            $this->renderSnippetInContext($rootSnippetSourceList, $context);
        }

        return $this->snippetList;
    }

    /**
     * @param RootSnippetSourceList $rootSnippetSourceList
     * @param $context
     */
    private function renderSnippetInContext(RootSnippetSourceList $rootSnippetSourceList, $context)
    {
        $snippetKey = $this->snippetKeyGenerator->getKeyForContext($context, []);
        $snippetContent = array_shift($rootSnippetSourceList->getNumItemsPrePageForContext($context));
        $snippet = Snippet::create($snippetKey, $snippetContent);
        $this->snippetList->add($snippet);
    }
}
