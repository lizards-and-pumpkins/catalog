<?php

namespace Brera\Product;

use Brera\Context\ContextBuilder;
use Brera\Product\Exception\MalformedProductListingSourceJsonException;

class ProductListingSourceListBuilder
{
    /**
     * @var ContextBuilder
     */
    private $contextBuilder;

    public function __construct(ContextBuilder $contextBuilder)
    {
        $this->contextBuilder = $contextBuilder;
    }

    /**
     * @param string $json
     * @return ProductListingSourceList
     */
    public function fromJson($json)
    {
        $sourceArray = json_decode($json, true);

        if (!isset($sourceArray['products_per_page'])) {
            throw new MalformedProductListingSourceJsonException(
                'Root snippet source list JSON is lacking "products_per_page" element.'
            );
        }

        if (!is_array($sourceArray['products_per_page'])) {
            throw new MalformedProductListingSourceJsonException(
                '"products_per_page" in root snippet source list JSON must be an array.'
            );
        }

        $sourceDataPairs = array_map(function ($productsPerPageData) {
            $this->validateProductsPerPageData($productsPerPageData);
            $context = $this->contextBuilder->createContext($productsPerPageData['context']);
            return ['context' => $context, 'numItemsPerPage' => $productsPerPageData['number']];
        }, $sourceArray['products_per_page']);

        return ProductListingSourceList::fromArray($sourceDataPairs);
    }

    /**
     * @param mixed[] $data
     */
    private function validateProductsPerPageData(array $data)
    {
        if (!isset($data['context'])) {
            throw new MalformedProductListingSourceJsonException(
                'Products per page JSON is lacking context data.'
            );
        }

        if (!is_array($data['context'])) {
            throw new MalformedProductListingSourceJsonException(
                'Products per page context data JSON must be an array.'
            );
        }

        if (!isset($data['number'])) {
            throw new MalformedProductListingSourceJsonException(
                'Products per page JSON is lacking products per page number.'
            );
        }

        if (!is_int($data['number'])) {
            throw new MalformedProductListingSourceJsonException(
                'Products per page number must be an integer.'
            );
        }
    }
}
