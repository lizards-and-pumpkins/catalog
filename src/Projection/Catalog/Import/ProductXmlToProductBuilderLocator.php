<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Product\ProductTypeCode;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Projection\Catalog\Import\Exception\InvalidProductTypeCodeForImportedProductException;
use LizardsAndPumpkins\Projection\Catalog\Import\Exception\NoMatchingProductTypeBuilderFactoryFoundException;
use LizardsAndPumpkins\Utils\XPathParser;

class ProductXmlToProductBuilderLocator
{
    /**
     * @var ProductXmlToProductBuilder[]
     */
    private $productTypeBuilderFactories;

    public function __construct(ProductXmlToProductBuilder ...$productTypeBuildersFactories)
    {
        $this->productTypeBuilderFactories = $productTypeBuildersFactories;
    }

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
     * @param XPathParser $parser
     * @return string
     */
    private function getTypeCodeFromXml(XPathParser $parser)
    {
        $typeNode = $parser->getXmlNodesArrayByXPath('/product/@type');
        return $this->getTypeCodeStringFromDomNodeArray($typeNode);
    }

    /**
     * @param mixed[] $nodeArray
     * @return string
     */
    private function getTypeCodeStringFromDomNodeArray(array $nodeArray)
    {
        if (1 !== count($nodeArray)) {
            throw new InvalidProductTypeCodeForImportedProductException(
                'There must be exactly one product type code attribute specified on the import product XML'
            );
        }

        return $nodeArray[0]['value'];
    }

    /**
     * @param ProductTypeCode $typeCode
     * @param XPathParser $parser
     * @return SimpleProductBuilder
     */
    private function createProductBuilderForProductType(ProductTypeCode $typeCode, XPathParser $parser)
    {
        $builder = $this->getProductBuilderForProductType($typeCode);
        return $builder->createProductBuilder($parser);
    }

    /**
     * @param ProductTypeCode $typeCode
     * @return ProductXmlToProductBuilder
     */
    private function getProductBuilderForProductType(ProductTypeCode $typeCode)
    {
        foreach ($this->productTypeBuilderFactories as $productTypeBuilderFactory) {
            if ($typeCode->isEqualTo($productTypeBuilderFactory->getSupportedProductTypeCode())) {
                return $productTypeBuilderFactory;
            }
        }
        $message = sprintf('No product type builder factory for the product type code "%s" was found', $typeCode);
        throw new NoMatchingProductTypeBuilderFactoryFoundException($message);
    }


}
