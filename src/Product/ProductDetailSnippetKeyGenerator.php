<?php


namespace Brera\Product;

use Brera\Context\Context;
use Brera\SnippetKeyGenerator;

class ProductDetailSnippetKeyGenerator implements SnippetKeyGenerator
{
    /**
     * @param Context $context
     * @param array $data
     * @return string
     */
    public function getKeyForContext(Context $context, array $data = [])
    {
        if (! array_key_exists('product_id', $data)) {
            throw new MissingProductIdException(sprintf(
                'The product ID needs to be specified when getting the snippet key'
            ));
        }
        return sprintf('product_detail_view_%s_%s', $data['product_id'], $context->getId());
    }

    /**
     * @return string[]
     */
    public function getContextPartsUsedForKey()
    {
        return ['website', 'language'];
    }
}
