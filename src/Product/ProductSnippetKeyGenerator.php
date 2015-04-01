<?php


namespace Brera\Product;

use Brera\Context\Context;
use Brera\InvalidSnippetCodeException;
use Brera\SnippetKeyGenerator;

class ProductSnippetKeyGenerator implements SnippetKeyGenerator
{
    /**
     * @var string
     */
    private $snippetCode;

    /**
     * @param string $snippetCode
     */
    public function __construct($snippetCode)
    {
        if (! is_string($snippetCode)) {
            throw new InvalidSnippetCodeException(sprintf(
                'The snippet code for the ProductSnippetKeyGenerator has to be a string'
            ));
        }
        $this->snippetCode = $snippetCode;
    }
    
    /**
     * @param Context $context
     * @param array $data
     * @return string
     */
    public function getKeyForContext(Context $context, array $data = [])
    {
        if (! array_key_exists('product_id', $data)) {
            throw new MissingProductIdException(sprintf(
                'The product ID needs to be specified when getting a product snippet key'
            ));
        }
        return sprintf('%s_%s_%s', $this->snippetCode, $data['product_id'], $context->getId());
    }

    /**
     * @return string[]
     */
    public function getContextPartsUsedForKey()
    {
        return ['website', 'language'];
    }
}
