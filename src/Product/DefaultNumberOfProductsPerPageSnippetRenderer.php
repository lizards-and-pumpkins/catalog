<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
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
     * @param int $numberOfProductsPerPage
     * @param ContextSource $contextSource
     * @return SnippetList
     */
    public function render($numberOfProductsPerPage, ContextSource $contextSource)
    {
        $this->validateNumberOfProductsPerPage($numberOfProductsPerPage);

        $contextParts = $this->snippetKeyGenerator->getContextPartsUsedForKey();
        $contexts = $contextSource->getContextsForParts($contextParts);
        foreach ($contexts as $context) {
            $this->renderSnippetInContext($numberOfProductsPerPage, $context);
        }

        return $this->snippetList;
    }

    /**
     * @param $numberOfProductsPerPage
     */
    private function validateNumberOfProductsPerPage($numberOfProductsPerPage)
    {
        if (!is_int($numberOfProductsPerPage)) {
            throw new InvalidNumberOfProductsPerPageException(
                sprintf('Number or products per page has to be integer, got "%s".', gettype($numberOfProductsPerPage))
            );
        }
    }

    /**
     * @param int $numberOfProductsPerPage
     * @param $context
     */
    private function renderSnippetInContext($numberOfProductsPerPage, $context)
    {
        $snippetKey = $this->snippetKeyGenerator->getKeyForContext($context, []);
        $snippet = Snippet::create($snippetKey, $numberOfProductsPerPage);
        $this->snippetList->add($snippet);
    }
}
