<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Product\ProductTypeCode;
use LizardsAndPumpkins\Product\ProductId;
use LizardsAndPumpkins\Projection\Catalog\Import\Exception\InvalidProductTypeCodeForImportedProductException;
use LizardsAndPumpkins\Projection\Catalog\Import\Exception\InvalidProductTypeFactoryMethodException;
use LizardsAndPumpkins\Utils\XPathParser;

class ProductXmlToProductBuilderLocator extends ProductXmlToProductBuilder
{
    /**
     * @var callable[]
     */
    private $customProductTypeBuilders;

    /**
     * @param callable[] $customProductTypeBuildersFactoryMethods
     */
    public function __construct(array $customProductTypeBuildersFactoryMethods = [])
    {
        $this->validateFactoryMethods($customProductTypeBuildersFactoryMethods);
        $this->customProductTypeBuilders = $customProductTypeBuildersFactoryMethods;
    }

    /**
     * @param callable[] $customProductTypeBuildersFactoryMethods
     */
    private function validateFactoryMethods(array $customProductTypeBuildersFactoryMethods)
    {
        array_map(function ($factoryMethod) {
            if (!is_callable($factoryMethod)) {
                throw $this->createInvalidProductTypeFactoryException($factoryMethod);
            }
        }, $customProductTypeBuildersFactoryMethods);
    }

    /**
     * @param mixed $invalidFactoryMethod
     * @return InvalidProductTypeFactoryMethodException
     */
    private function createInvalidProductTypeFactoryException($invalidFactoryMethod)
    {
        $message = sprintf(
            'Custom product type builder factory methods have to be callable, got "%s"',
            $this->getVariableStringRepresentation($invalidFactoryMethod)
        );
        return new InvalidProductTypeFactoryMethodException($message);
    }

    /**
     * @param mixed $variable
     * @return string
     */
    private function getVariableStringRepresentation($variable)
    {
        if (is_string($variable)) {
            return $variable;
        }
        return is_object($variable) ?
            get_class($variable) :
            gettype($variable);
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
        if (isset($this->customProductTypeBuilders[(string) $typeCode])) {
            $method = $this->customProductTypeBuilders[(string) $typeCode];
            return $method($parser);
        }
        $method = 'create' . ucwords($typeCode) . 'ProductBuilder';
        return $this->{$method}($parser);
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

    /**
     * @param XPathParser $parser
     * @return ConfigurableProductBuilder
     */
    private function createConfigurableProductBuilder(XPathParser $parser)
    {
        $simpleProductBuilder = $this->createSimpleProductBuilder($parser);
        return new ConfigurableProductBuilder($simpleProductBuilder);
    }
}
