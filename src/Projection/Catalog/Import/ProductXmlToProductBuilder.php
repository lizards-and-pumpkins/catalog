<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Utils\XPathParser;
use LizardsAndPumpkins\Product\ProductAttribute;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Projection\Catalog\Import\Exception\InvalidNumberOfSkusForImportedProductException;

abstract class ProductXmlToProductBuilder
{
    /**
     * @param XPathParser $parser
     * @return string
     */
    protected function getSkuFromXml(XPathParser $parser)
    {
        $skuNode = $parser->getXmlNodesArrayByXPath('/product/@sku');
        return $this->getSkuStringFromDomNodeArray($skuNode);
    }

    /**
     * @param XPathParser $parser
     * @return ProductAttributeListBuilder
     */
    protected function createProductAttributeListBuilder(XPathParser $parser)
    {
        $attributeNodes = $parser->getXmlNodesArrayByXPath('/product/attributes/*');
        $attributesArray = array_map([$this, 'nodeArrayAsAttributeArray'], $attributeNodes);
        return ProductAttributeListBuilder::fromArray($attributesArray);
    }

    /**
     * @param XPathParser $parser
     * @param ProductId $productId
     * @return ProductImageListBuilder
     */
    protected function createProductImageListBuilder(XPathParser $parser, ProductId $productId)
    {
        $imagesNodes = $parser->getXmlNodesArrayByXPath('/product/images/image');
        $imagesArray = array_map(function (array $imageNode) {
            return $imageNode['value'];
        }, array_map([$this, 'nodeArrayAsAttributeArray'], $imagesNodes));
        return ProductImageListBuilder::fromArray($productId, $imagesArray);
    }

    /**
     * @param mixed[] $nodeArray
     * @return string
     */
    private function getSkuStringFromDomNodeArray(array $nodeArray)
    {
        if (1 !== count($nodeArray)) {
            throw new InvalidNumberOfSkusForImportedProductException(
                'There must be exactly one SKU in the imported product XML'
            );
        }

        return $nodeArray[0]['value'];
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
}
