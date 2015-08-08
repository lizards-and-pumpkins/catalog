<?php

namespace Brera;

use Brera\Context\ContextBuilder;

class RootSnippetSourceListBuilder
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
     * @return RootSnippetSourceList
     */
    public function fromJson($json)
    {
        $sourceArray = json_decode($json, true);

        if (!isset($sourceArray['products_per_page'])) {
            throw new MalformedProductListingRootSnippetJsonException(
                'Root snippet source list JSON is lacking "products_per_page" element.'
            );
        }

        $sourceDataPairs = array_map(function ($productsPerPageData) {
            $this->validateProductsPerPageData($productsPerPageData);
            $context = $this->contextBuilder->getContext($productsPerPageData['context']);
            return ['context' => $context, 'numItemsPerPage' => (int) $productsPerPageData['number']];
        }, $sourceArray['products_per_page']);

        return RootSnippetSourceList::fromArray($sourceDataPairs);
    }

    /**
     * @param array $data
     */
    private function validateProductsPerPageData(array $data)
    {
        if (!isset($data['context'])) {
            throw new MalformedProductListingRootSnippetJsonException(
                'Products pre page JSON is lacking context data.'
            );
        }

        if (!is_array($data['context'])) {
            throw new MalformedProductListingRootSnippetJsonException(
                'Products pre page context data JSON must be an array.'
            );
        }

        if (!isset($data['number'])) {
            throw new MalformedProductListingRootSnippetJsonException(
                'Products pre page JSON is lacking products per page number.'
            );
        }

        if (!is_int($data['number'])) {
            throw new MalformedProductListingRootSnippetJsonException(
                'Products pre page number must be an integer.'
            );
        }
    }
}
