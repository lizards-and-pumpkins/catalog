<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Projection\Catalog\Import\Exception\InvalidNumberOfSkusPerImportedProductException;
use LizardsAndPumpkins\Product\ProductAttribute;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Utils\XPathParser;

class ProductXmlToProductBuilder
{
    /**
     * @param string $xml
     * @return ProductBuilder
     */
    public function createProductBuilderFromXml($xml)
    {
        $parser = new XPathParser($xml);

        $skuNode = $parser->getXmlNodesArrayByXPath('/product/@sku');
        $skuString = $this->getSkuStringFromDomNodeArray($skuNode);
        $productId = ProductId::fromString($skuString);

        $attributeListBuilder = $this->createProductAttributeListBuilder($parser);

        $imageListBuilder = $this->createProductImageListBuilder($parser, $productId);

        return new ProductBuilder($productId, $attributeListBuilder, $imageListBuilder);
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

    /**
     * @param XPathParser $parser
     * @return ProductAttributeListBuilder
     */
    private function createProductAttributeListBuilder(XPathParser $parser)
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
    private function createProductImageListBuilder(XPathParser $parser, ProductId $productId)
    {
        $imagesNodes = $parser->getXmlNodesArrayByXPath('/product/images/image');
        $imagesArray = array_map(function (array $imageNode) {
            return $imageNode['value'];
        }, array_map([$this, 'nodeArrayAsAttributeArray'], $imagesNodes));
        return ProductImageListBuilder::fromArray($productId, $imagesArray);
    }
}
