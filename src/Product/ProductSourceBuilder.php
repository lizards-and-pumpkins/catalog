<?php

namespace Brera\Product;

use Brera\Utils\XPathParser;

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
        $productId = ProductId::fromString($skuString);

        $attributeNodes = $parser->getXmlNodesArrayByXPath('/product/attributes/*');
        $attributeList = ProductAttributeList::fromArray($attributeNodes);

        return new ProductSource($productId, $attributeList);
    }

    /**
     * @param mixed[] $nodeArray
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
