<?php

namespace Brera\Product;

use Brera\SnippetKeyGenerator;
use Brera\InvalidSnippetKeyIdentifierException;
use Brera\Environment\Environment;
use Brera\Environment\VersionedEnvironment;

class ProductDetailViewSnippetKeyGenerator implements SnippetKeyGenerator
{
    const KEY_PREFIX = 'product_detail_view';

    /**
     * @param mixed|ProductId $productId
     * @param Environment $environment
     * @throws InvalidSnippetKeyIdentifierException
     * @return string
     */
    public function getKeyForEnvironment($productId, Environment $environment)
    {
        if (!($productId instanceof ProductId)) {
            throw new InvalidSnippetKeyIdentifierException(sprintf(
                'Expected instance of ProductId, but got "%s"',
                is_scalar($productId) ? $productId : gettype($productId)
            ));
        }

        return $this->getKeyForProductIdInEnvironment($productId, $environment);
    }

    /**
     * @param ProductId $productId
     * @param Environment $environment
     * @return string
     */
    private function getKeyForProductIdInEnvironment(ProductId $productId, Environment $environment)
    {
        return sprintf('%s_%s_%s', self::KEY_PREFIX, $environment->getValue(VersionedEnvironment::CODE), $productId);
    }
}
