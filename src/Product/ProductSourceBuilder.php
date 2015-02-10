<?php

namespace Brera\Product;

use Brera\XPathParser;

class ProductSourceBuilder
{
    /**
     * @param string $xml
     * @return ProductSource
     */
    public function createProductSourceFromXml($xml)
    {
        $parser = new XPathParser($xml);

        $skuNode = $parser->getXmlNodesArrayByXPath('/product/@sku');
        $skuString = $this->getSkuStringFromDomNodeArray($skuNode);
        $sku = PoCSku::fromString($skuString);
        $productId = ProductId::fromSku($sku);

        $attributeNodes = $parser->getXmlNodesArrayByXPath('/product/attributes/*');
        $attributeList = ProductAttributeList::fromArray($attributeNodes);

        return new ProductSource($productId, $attributeList);
    }

    /**
     * @param array $nodeArray
     * @return string
     */
    private function getSkuStringFromDomNodeArray(array $nodeArray)
    {
        if (1 !== count($nodeArray)) {
            throw new InvalidNumberOfSkusPerImportedProductException(
                'There must be exactly one SKU in the imported product XML'
            );
        }

        return $nodeArray[0]['value'];
    }
}
