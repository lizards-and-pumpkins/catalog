<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Exception\InvalidNumberOfSkusPerImportedProductException;
use LizardsAndPumpkins\Utils\XPathParser;

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
        $attributesArray = array_map([$this, 'nodeArrayAsAttributeArray'], $attributeNodes);
        $attributeList = ProductAttributeList::fromArray($attributesArray);

        return new ProductSource($productId, $attributeList);
    }
    
    /**
     * @param mixed[] $node
     * @return mixed[]
     */
    private function nodeArrayAsAttributeArray(array $node)
    {
        $value = !is_array($node['value']) ?
            $node['value'] :
            array_map([$this, 'nodeArrayAsAttributeArray'], $node['value']);
        return [
            ProductAttribute::CODE => $node['nodeName'],
            ProductAttribute::CONTEXT_DATA => $node['attributes'],
            ProductAttribute::VALUE => $value,
        ];
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
