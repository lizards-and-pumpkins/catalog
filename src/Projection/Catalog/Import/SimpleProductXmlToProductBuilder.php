<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Product\ProductAttribute;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Product\ProductTypeCode;
use LizardsAndPumpkins\Product\SimpleProduct;
use LizardsAndPumpkins\Projection\Catalog\Import\Exception\InvalidNumberOfSkusForImportedProductException;
use LizardsAndPumpkins\Projection\Catalog\Import\Exception\TaxClassAttributeMissingForImportedProductException;
use LizardsAndPumpkins\Utils\XPathParser;

class SimpleProductXmlToProductBuilder implements ProductXmlToProductBuilder
{
    /**
     * @return ProductTypeCode
     */
    public function getSupportedProductTypeCode()
    {
        return ProductTypeCode::fromString(SimpleProduct::TYPE_CODE);
    }
    
    /**
     * @param XPathParser $parser
     * @return SimpleProductBuilder
     */
    public function createProductBuilder(XPathParser $parser)
    {
        $productId = ProductId::fromString($this->getSkuFromXml($parser));
        $taxClass = $this->getTaxClassFromXml($parser);
        $attributeListBuilder = $this->createProductAttributeListBuilder($parser);
        $imageListBuilder = $this->createProductImageListBuilder($parser, $productId);
        return new SimpleProductBuilder($productId, $attributeListBuilder, $imageListBuilder);
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
    public function getTaxClassFromXml(XPathParser $parser)
    {
        $skuNode = $parser->getXmlNodesArrayByXPath('/product/@tax_class');
        return $this->getTaxClassStringFromDomNodeArray($skuNode);
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
     * @param mixed[] $nodeArray
     * @return string
     */
    private function getTaxClassStringFromDomNodeArray(array $nodeArray)
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
    private function nodeArrayAsAttributeArray(array $node)
    {
        $value = !is_array($node['value']) ?
            $node['value'] :
            array_map([$this, 'nodeArrayAsAttributeArray'], $node['value']);
        return [
            ProductAttribute::CODE => $node['nodeName'],
            ProductAttribute::CONTEXT => $node['attributes'],
            ProductAttribute::VALUE => $value,
        ];
    }
}
