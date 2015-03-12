<?php

namespace Brera\Product;

use Brera\InvalidSnippetKeyIdentifierException;
use Brera\Context\Context;
use Brera\SnippetKeyGenerator;

class ProductSnippetKeyGenerator implements SnippetKeyGenerator
{
    /**
     * @param string $snippetCode
     * @param mixed|ProductId $productId
     * @param Context $context
     * @return string
     * @throws InvalidSnippetKeyIdentifierException
     */
    public function getKeyForContext($snippetCode, $productId, Context $context)
    {
        if (!($productId instanceof ProductId)) {
            throw new InvalidSnippetKeyIdentifierException(sprintf(
                'Expected instance of ProductId, but got "%s"',
                is_scalar($productId) ? $productId : gettype($productId)
            ));
        }

        return $this->getKeyForProductIdInContext($snippetCode, $productId, $context);
    }

    /**
     * @param string $snippetCode
     * @param ProductId $productId
     * @param Context $context
     * @return string
     */
    private function getKeyForProductIdInContext($snippetCode, ProductId $productId, Context $context)
    {
        return sprintf('%s_%s_%s', $snippetCode, $productId, $context->getId());
    }
}
