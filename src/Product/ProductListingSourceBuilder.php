<?php

namespace Brera\Product;

use Brera\Utils\XPathParser;

class ProductListingSourceBuilder
{
    /**
     * @param string $xml
     * @return ProductListingSource
     */
    public function createProductListingSourceFromXml($xml)
    {
        $parser = new XPathParser($xml);

        $urlKeyNode = $parser->getXmlNodesArrayByXPath('/listing/@url_key');
        $urlKey = $this->getUrlKeyStringFromDomNodeArray($urlKeyNode);

        $contextData = [];
        $xmlNodeAttributes = $parser->getXmlNodesArrayByXPath('/listing/@*');

        foreach ($xmlNodeAttributes as $xmlAttribute) {
            if ('url_key' !== $xmlAttribute['nodeName']) {
                $contextData[$xmlAttribute['nodeName']] = $xmlAttribute['value'];
            }
        }

        $criteria = [];
        $criteriaNodes = $parser->getXmlNodesArrayByXPath('/listing/*');

        foreach ($criteriaNodes as $attributeNode) {
            $criteria[$attributeNode['nodeName']] = $attributeNode['value'];
        }

        return new ProductListingSource($urlKey, $contextData, $criteria);
    }

    /**
     * @param mixed[] $nodeArray
     * @return string
     */
    private function getUrlKeyStringFromDomNodeArray(array $nodeArray)
    {
        if (1 !== count($nodeArray)) {
            throw new InvalidNumberOfUrlKeysPerImportedProductListingException(
                'There must be exactly one URL key in the imported product listing XML'
            );
        }

        return $nodeArray[0]['value'];
    }
}
