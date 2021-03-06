<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Import\Product\Image\ProductImageListBuilder;
use LizardsAndPumpkins\Import\Tax\ProductTaxClass;
use LizardsAndPumpkins\Import\Product\Exception\InvalidNumberOfSkusForImportedProductException;
use LizardsAndPumpkins\Import\Product\Exception\TaxClassAttributeMissingForImportedProductException;
use LizardsAndPumpkins\Import\XPathParser;

class SimpleProductXmlToProductBuilder implements ProductXmlToProductBuilder
{
    public function getSupportedProductTypeCode() : ProductTypeCode
    {
        return ProductTypeCode::fromString(SimpleProduct::TYPE_CODE);
    }
    
    public function createProductBuilder(XPathParser $parser) : ProductBuilder
    {
        $productId = new ProductId($this->getSkuFromXml($parser));
        $taxClass = ProductTaxClass::fromString($this->getTaxClassFromXml($parser));
        $attributeListBuilder = $this->createProductAttributeListBuilder($parser);
        $imageListBuilder = $this->createProductImageListBuilder($parser, $productId);
        return new SimpleProductBuilder($productId, $taxClass, $attributeListBuilder, $imageListBuilder);
    }

    private function getSkuFromXml(XPathParser $parser) : string
    {
        $skuNode = $parser->getXmlNodesArrayByXPath('/product/@sku');
        return $this->getSkuStringFromDomNodeArray($skuNode);
    }

    public function getTaxClassFromXml(XPathParser $parser) : string
    {
        $skuNode = $parser->getXmlNodesArrayByXPath('/product/@tax_class');
        return $this->getTaxClassStringFromDomNodeArray($skuNode);
    }

    private function createProductAttributeListBuilder(XPathParser $parser) : ProductAttributeListBuilder
    {
        $attributeNodes = $parser->getXmlNodesArrayByXPath('/product/attributes/attribute');
        $attributesArray = array_map([$this, 'nodeArrayAsAttributeArray'], $attributeNodes);
        return ProductAttributeListBuilder::fromArray($attributesArray);
    }

    private function createProductImageListBuilder(XPathParser $parser, ProductId $productId) : ProductImageListBuilder
    {
        $imagesNodes = $parser->getXmlNodesArrayByXPath('/product/images/image');
        $imagesArray = array_map(function (array $imageNode) {
            return $imageNode['value'];
        }, array_map([$this, 'nodeArrayAsAttributeArray'], $imagesNodes));
        return ProductImageListBuilder::fromImageArrays($productId, ...$imagesArray);
    }

    /**
     * @param mixed[] $nodeArray
     * @return string
     */
    private function getSkuStringFromDomNodeArray(array $nodeArray) : string
    {
        if (1 !== count($nodeArray)) {
            throw new InvalidNumberOfSkusForImportedProductException(
                'There must be exactly one SKU in the imported product XML'
            );
        }

        return $nodeArray[0]['value'];
    }

    /**
     * @param mixed[] $nodeArray
     * @return string
     */
    private function getTaxClassStringFromDomNodeArray(array $nodeArray) : string
    {
        if (1 !== count($nodeArray)) {
            throw new TaxClassAttributeMissingForImportedProductException(
                'The tax_class attribute is missing in the imported product XML'
            );
        }

        return $nodeArray[0]['value'];
    }

    /**
     * @param mixed[] $node
     * @return mixed[]
     */
    private function nodeArrayAsAttributeArray(array $node) : array
    {
        return [
            ProductAttribute::CODE => $this->getCode($node),
            ProductAttribute::CONTEXT => $this->getContextParts($node),
            ProductAttribute::VALUE => $this->getValue($node),
        ];
    }

    /**
     * @param mixed[] $node
     * @return string
     */
    private function getCode(array $node) : string
    {
        if ('attribute' === $node['nodeName']) {
            return $node['attributes']['name'];
        }

        return $node['nodeName'];
    }

    /**
     * @param array[] $node
     * @return string[]
     */
    private function getContextParts(array $node) : array
    {
        if ('attribute' === $node['nodeName']) {
            return array_diff_key($node['attributes'], ['name' => null]);
        }

        return $node['attributes'];
    }

    /**
     * @param mixed[] $node
     * @return mixed
     */
    private function getValue(array $node)
    {
        if (is_array($node['value'])) {
            return array_map([$this, 'nodeArrayAsAttributeArray'], $node['value']);
        }

        return $node['value'];
    }
}
