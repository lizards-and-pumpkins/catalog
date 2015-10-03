<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Product\ProductTypeCode;
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

        $productTypeCode = ProductTypeCode::fromString($this->getTypeCodeFromXml($parser));

        return $this->createProductBuilderForProductType($productTypeCode, $parser);
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

    /**
     * @param XPathParser $parser
     * @return string
     */
    private function getSkuFromXml(XPathParser $parser)
    {
        $skuNode = $parser->getXmlNodesArrayByXPath('/product/@sku');
        return $this->getSkuStringFromDomNodeArray($skuNode);
    }

    /**
     * @param XPathParser $parser
     * @return string
     */
    private function getTypeCodeFromXml(XPathParser $parser)
    {
        $skuNode = $parser->getXmlNodesArrayByXPath('/product/@type');
        return $this->getSkuStringFromDomNodeArray($skuNode);
    }

    /**
     * @param ProductTypeCode $typeCode
     * @param XPathParser $parser
     * @return SimpleProductBuilder
     */
    private function createProductBuilderForProductType(ProductTypeCode $typeCode, XPathParser $parser)
    {
        return $this->createSimpleProductBuilder($parser);
    }

    /**
     * @param XPathParser $parser
     * @return SimpleProductBuilder
     */
    private function createSimpleProductBuilder(XPathParser $parser)
    {
        $productId = ProductId::fromString($this->getSkuFromXml($parser));
        $attributeListBuilder = $this->createProductAttributeListBuilder($parser);
        $imageListBuilder = $this->createProductImageListBuilder($parser, $productId);
        return new SimpleProductBuilder($productId, $attributeListBuilder, $imageListBuilder);
    }
}
