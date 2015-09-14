<?php

namespace Brera\Product;

use Brera\Product\Exception\InvalidProductStockQuantitySourceDataException;
use Brera\Utils\XPathParser;

class ProductStockQuantitySourceBuilder
{
    /**
     * @param string $xml
     * @return ProductStockQuantitySource
     */
    public function createFromXml($xml)
    {
        $parser = new XPathParser($xml);

        $skuNodes = $parser->getXmlNodesArrayByXPath('/*/sku');
        $skuString = $this->getSkuStringFromDomNodeArray($skuNodes);

        $contextNodes = $parser->getXmlNodesArrayByXPath('/*/@*');
        $contextData = $this->getContextDataFromXmlNodeArray($contextNodes);

        $quantityNodes = $parser->getXmlNodesArrayByXPath('/*/quantity');
        $quantityString = $this->getQuantityStringFromDomNodeArray($quantityNodes);

        $productId = ProductId::fromString($skuString);
        $quantity = ProductStockQuantity::fromString($quantityString);

        return new ProductStockQuantitySource($productId, $contextData, $quantity);
    }

    /**
     * @param array[] $skuNodes
     * @return string
     */
    private function getSkuStringFromDomNodeArray(array $skuNodes)
    {
        if (1 !== count($skuNodes)) {
            throw new InvalidProductStockQuantitySourceDataException(
                'There must be just one "sku" node in product stock quantity source data.'
            );
        }

        return $skuNodes[0]['value'];
    }

    /**
     * @param array[] $quantityNodes
     * @return string
     */
    private function getQuantityStringFromDomNodeArray(array $quantityNodes)
    {
        if (1 !== count($quantityNodes)) {
            throw new InvalidProductStockQuantitySourceDataException(
                'There must be just one "quantity" node in product stock quantity source data.'
            );
        }

        return $quantityNodes[0]['value'];
    }

    /**
     * @param array[] $xmlNodeAttributes
     * @return string[]
     */
    private function getContextDataFromXmlNodeArray(array $xmlNodeAttributes)
    {
        $contextData = [];

        foreach ($xmlNodeAttributes as $xmlAttribute) {
            $contextData[$xmlAttribute['nodeName']] = $xmlAttribute['value'];
        }

        return $contextData;
    }
}
