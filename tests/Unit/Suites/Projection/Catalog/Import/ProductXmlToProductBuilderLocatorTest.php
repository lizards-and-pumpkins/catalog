<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Import\Product\ConfigurableProductBuilder;
use LizardsAndPumpkins\Import\Product\ConfigurableProductXmlToProductBuilder;
use LizardsAndPumpkins\Import\Product\Exception\InvalidNumberOfSkusForImportedProductException;
use LizardsAndPumpkins\Import\Product\ProductAttribute;
use LizardsAndPumpkins\Import\Product\Exception\InvalidProductTypeCodeForImportedProductException;
use LizardsAndPumpkins\Import\Product\Exception\NoMatchingProductTypeBuilderFactoryFoundException;
use LizardsAndPumpkins\Import\Product\Exception\TaxClassAttributeMissingForImportedProductException;
use LizardsAndPumpkins\Import\Product\ProductBuilder;
use LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilderLocator;
use LizardsAndPumpkins\Import\Product\SimpleProductBuilder;
use LizardsAndPumpkins\Import\Product\SimpleProductXmlToProductBuilder;

/**
 * @covers \LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilderLocator
 * @covers \LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilder
 * @covers \LizardsAndPumpkins\Import\Product\SimpleProductXmlToProductBuilder
 * @covers \LizardsAndPumpkins\Import\Product\ConfigurableProductXmlToProductBuilder
 * @covers \LizardsAndPumpkins\Import\Product\ConfigurableProductXmlToAssociatedProductListBuilder
 * @covers \LizardsAndPumpkins\Import\Product\ConfigurableProductXmlToVariationAttributeList
 * @uses   \LizardsAndPumpkins\Import\Product\SimpleProductBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\ConfigurableProductBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeListBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageListBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\Image\ProductImageBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\Composite\AssociatedProductListBuilder
 * @uses   \LizardsAndPumpkins\Import\Product\ProductId
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Import\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Import\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Import\Product\ProductTypeCode
 * @uses   \LizardsAndPumpkins\Import\Tax\ProductTaxClass
 * @uses   \LizardsAndPumpkins\Import\Product\Composite\ProductVariationAttributeList
 * @uses   \LizardsAndPumpkins\Import\XPathParser
 */
class ProductXmlToProductBuilderLocatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductXmlToProductBuilderLocator
     */
    private $xmlToProductBuilder;

    /**
     * @var \DOMDocument
     */
    private $domDocument;

    /**
     * @param mixed $expected
     * @param ProductBuilder $productBuilder
     * @param string $attributeCode
     */
    private function assertFirstProductAttributeInAListValueEquals(
        $expected,
        ProductBuilder $productBuilder,
        $attributeCode
    ) {
        $attributes = $this->getAttributesWithCodeFromInstance($productBuilder, $attributeCode);
        $this->assertNotEmpty($attributes);
        $this->assertEquals($expected, $attributes[0]->getValue());
    }

    /**
     * @param ProductBuilder $productBuilder
     * @param string $attributeCode
     * @return ProductAttribute[]
     */
    private function getAttributesWithCodeFromInstance(ProductBuilder $productBuilder, $attributeCode)
    {
        $attributes = $this->getAttributesArrayFromInstance($productBuilder);
        return array_values(array_filter($attributes, function (ProductAttribute $attribute) use ($attributeCode) {
            return $attribute->isCodeEqualTo($attributeCode);
        }));
    }

    /**
     * @param ProductBuilder $productBuilder
     * @return ProductAttribute[]
     */
    private function getAttributesArrayFromInstance(ProductBuilder $productBuilder)
    {
        $attributeListBuilder = $this->getPrivatePropertyValue($productBuilder, 'attributeListBuilder');
        return $this->getPrivatePropertyValue($attributeListBuilder, 'attributes');
    }

    /**
     * @param object $object
     * @param string $propertyName
     * @return mixed
     */
    private function getPrivatePropertyValue($object, $propertyName)
    {
        $property = new \ReflectionProperty($object, $propertyName);
        $property->setAccessible(true);
        return $property->getValue($object);
    }

    /**
     * @return string
     */
    private function getSimpleProductXml()
    {
        $xpath = new \DOMXPath($this->domDocument);
        $xpath->registerNamespace('lp', 'http://lizardsandpumpkins.com');
        $firstSimpleProduct = $xpath->query("/lp:catalog/lp:products/lp:product[@type='simple'][1]")[0];
        return $this->domDocument->saveXML($firstSimpleProduct);
    }

    /**
     * @return string
     */
    private function getConfigurableProductXml()
    {
        $xpath = new \DOMXPath($this->domDocument);
        $xpath->registerNamespace('lp', 'http://lizardsandpumpkins.com');
        $firstConfigurableProduct = $xpath->query("/lp:catalog/lp:products/lp:product[@type='configurable'][1]")[0];
        return $this->domDocument->saveXML($firstConfigurableProduct);
    }

    /**
     * @param string $productXml
     * @return string
     */
    private function getSpecialPriceFromProductXml($productXml)
    {
        $domDocument = new \DOMDocument();
        $domDocument->loadXML($productXml);
        $domXPath = (new \DOMXPath($domDocument));

        return $domXPath->query('//attributes/attribute[@name="special_price"]')->item(0)->nodeValue;
    }
    
    /**
     * @return ProductXmlToProductBuilderLocator
     */
    private function createProductXmlToProductBuilderLocatorInstance()
    {
        $productXmlToProductBuilderLocatorProxy = function () {
            return $this->createProductXmlToProductBuilderLocatorInstance();
        };
        return new ProductXmlToProductBuilderLocator(
            new SimpleProductXmlToProductBuilder(),
            new ConfigurableProductXmlToProductBuilder($productXmlToProductBuilderLocatorProxy)
        );
    }

    protected function setUp()
    {
        $this->xmlToProductBuilder = $this->createProductXmlToProductBuilderLocatorInstance();

        $xml = file_get_contents(__DIR__ . '/../../../../../shared-fixture/catalog.xml');
        $this->domDocument = new \DOMDocument();
        $this->domDocument->loadXML($xml);
    }

    public function testSimpleProductBuilderIsCreatedFromXml()
    {
        $simpleProductXml = $this->getSimpleProductXml();
        $expectedSpecialPrice = $this->getSpecialPriceFromProductXml($simpleProductXml);

        $productBuilder = $this->xmlToProductBuilder->createProductBuilderFromXml($simpleProductXml);

        $this->assertInstanceOf(SimpleProductBuilder::class, $productBuilder);
        $this->assertFirstProductAttributeInAListValueEquals($expectedSpecialPrice, $productBuilder, 'special_price');
    }

    public function testConfigurableProductBuilderIsCreatedFromXml()
    {
        $configurableProductXml = $this->getConfigurableProductXml();

        $productBuilder = $this->xmlToProductBuilder->createProductBuilderFromXml($configurableProductXml);

        $this->assertInstanceOf(ConfigurableProductBuilder::class, $productBuilder);
    }

    public function testProductBuilderIsCreatedFromXmlIgnoringAssociatedProductAttributes()
    {
        $configurableProductXml = $this->getConfigurableProductXml();

        $productBuilder = $this->xmlToProductBuilder->createProductBuilderFromXml($configurableProductXml);
        $simpleProductBuilderDelegate = $this->getPrivatePropertyValue($productBuilder, 'simpleProductBuilder');
        $sizeAttributes = $this->getAttributesWithCodeFromInstance($simpleProductBuilderDelegate, 'size');
        $this->assertEmpty($sizeAttributes, 'The configurable product builder has "size" attributes');
        $colorAttributes = $this->getAttributesWithCodeFromInstance($simpleProductBuilderDelegate, 'color');
        $this->assertEmpty($colorAttributes, 'The configurable product builder has "color" attributes');
    }

    public function testExceptionIsThrownIfSkuIsMissing()
    {
        $this->expectException(InvalidNumberOfSkusForImportedProductException::class);
        $xml = '<product type="simple" tax_class="test"></product>';

        $this->createProductXmlToProductBuilderLocatorInstance()->createProductBuilderFromXml($xml);
    }

    public function testExceptionIsThrownIfProductTypeCodeIsMissing()
    {
        $this->expectException(InvalidProductTypeCodeForImportedProductException::class);
        $xml = '<product sku="foo" tax_class="test"></product>';

        $this->createProductXmlToProductBuilderLocatorInstance()->createProductBuilderFromXml($xml);
    }

    public function testExceptionIsThrownIfTaxClassIsMissing()
    {
        $this->expectException(TaxClassAttributeMissingForImportedProductException::class);
        $xml = '<product sku="foo" type="simple"></product>';

        $this->createProductXmlToProductBuilderLocatorInstance()->createProductBuilderFromXml($xml);
    }

    public function testExceptionIsThrownIfNoFactoryForGivenTypeCodeIsFound()
    {
        $this->expectException(NoMatchingProductTypeBuilderFactoryFoundException::class);
        $this->expectExceptionMessage('No product type builder factory for the product type code "invalid" was found');
        $xml = '<product type="invalid" sku="test" tax_class="test"></product>';

        $this->createProductXmlToProductBuilderLocatorInstance()->createProductBuilderFromXml($xml);
    }
}
