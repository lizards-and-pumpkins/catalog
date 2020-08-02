<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Import\Product\Exception\InvalidNumberOfSkusForImportedProductException;
use LizardsAndPumpkins\Import\Product\Exception\InvalidProductTypeCodeForImportedProductException;
use LizardsAndPumpkins\Import\Product\Exception\NoMatchingProductTypeBuilderFactoryFoundException;
use LizardsAndPumpkins\Import\Product\Exception\TaxClassAttributeMissingForImportedProductException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\ProductXmlToProductBuilderLocator
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
class ProductXmlToProductBuilderLocatorTest extends TestCase
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
        string $attributeCode
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
    private function getAttributesWithCodeFromInstance(ProductBuilder $productBuilder, string $attributeCode) : array
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
    private function getAttributesArrayFromInstance(ProductBuilder $productBuilder) : array
    {
        $attributeListBuilder = $this->getPrivatePropertyValue($productBuilder, 'attributeListBuilder');
        return $this->getPrivatePropertyValue($attributeListBuilder, 'attributes');
    }

    /**
     * @param object $object
     * @param string $propertyName
     * @return mixed
     */
    private function getPrivatePropertyValue($object, string $propertyName)
    {
        $property = new \ReflectionProperty($object, $propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    private function getSimpleProductXml() : string
    {
        $xpath = new \DOMXPath($this->domDocument);
        $xpath->registerNamespace('lp', 'http://lizardsandpumpkins.com');
        $firstSimpleProduct = $xpath->query("/lp:catalog/lp:products/lp:product[@type='simple'][1]")[0];
        return $this->domDocument->saveXML($firstSimpleProduct);
    }

    private function getConfigurableProductXml() : string
    {
        $xpath = new \DOMXPath($this->domDocument);
        $xpath->registerNamespace('lp', 'http://lizardsandpumpkins.com');
        $firstConfigurableProduct = $xpath->query("/lp:catalog/lp:products/lp:product[@type='configurable'][1]")[0];
        return $this->domDocument->saveXML($firstConfigurableProduct);
    }

    private function getSpecialPriceFromProductXml(string $productXml) : string
    {
        $domDocument = new \DOMDocument();
        $domDocument->loadXML($productXml);
        $domXPath = (new \DOMXPath($domDocument));

        return $domXPath->query('//attributes/attribute[@name="special_price"]')->item(0)->nodeValue;
    }
    
    private function createProductXmlToProductBuilderLocatorInstance() : ProductXmlToProductBuilderLocator
    {
        $productXmlToProductBuilderLocatorProxy = function () {
            return $this->createProductXmlToProductBuilderLocatorInstance();
        };
        return new ProductXmlToProductBuilderLocator(
            new SimpleProductXmlToProductBuilder(),
            new ConfigurableProductXmlToProductBuilder($productXmlToProductBuilderLocatorProxy)
        );
    }

    final protected function setUp(): void
    {
        $this->xmlToProductBuilder = $this->createProductXmlToProductBuilderLocatorInstance();

        $xml = file_get_contents(__DIR__ . '/../../../../shared-fixture/catalog.xml');
        $this->domDocument = new \DOMDocument();
        $this->domDocument->loadXML($xml);
    }

    public function testSimpleProductBuilderIsCreatedFromXml(): void
    {
        $simpleProductXml = $this->getSimpleProductXml();
        $expectedSpecialPrice = $this->getSpecialPriceFromProductXml($simpleProductXml);

        $productBuilder = $this->xmlToProductBuilder->createProductBuilderFromXml($simpleProductXml);

        $this->assertInstanceOf(SimpleProductBuilder::class, $productBuilder);
        $this->assertFirstProductAttributeInAListValueEquals($expectedSpecialPrice, $productBuilder, 'special_price');
    }

    public function testConfigurableProductBuilderIsCreatedFromXml(): void
    {
        $configurableProductXml = $this->getConfigurableProductXml();

        $productBuilder = $this->xmlToProductBuilder->createProductBuilderFromXml($configurableProductXml);

        $this->assertInstanceOf(ConfigurableProductBuilder::class, $productBuilder);
    }

    public function testProductBuilderIsCreatedFromXmlIgnoringAssociatedProductAttributes(): void
    {
        $configurableProductXml = $this->getConfigurableProductXml();

        $productBuilder = $this->xmlToProductBuilder->createProductBuilderFromXml($configurableProductXml);
        $simpleProductBuilderDelegate = $this->getPrivatePropertyValue($productBuilder, 'simpleProductBuilder');
        $sizeAttributes = $this->getAttributesWithCodeFromInstance($simpleProductBuilderDelegate, 'size');
        $this->assertEmpty($sizeAttributes, 'The configurable product builder has "size" attributes');
        $colorAttributes = $this->getAttributesWithCodeFromInstance($simpleProductBuilderDelegate, 'color');
        $this->assertEmpty($colorAttributes, 'The configurable product builder has "color" attributes');
    }

    public function testExceptionIsThrownIfSkuIsMissing(): void
    {
        $this->expectException(InvalidNumberOfSkusForImportedProductException::class);
        $xml = '<product type="simple" tax_class="test"></product>';

        $this->createProductXmlToProductBuilderLocatorInstance()->createProductBuilderFromXml($xml);
    }

    public function testExceptionIsThrownIfProductTypeCodeIsMissing(): void
    {
        $this->expectException(InvalidProductTypeCodeForImportedProductException::class);
        $xml = '<product sku="foo" tax_class="test"></product>';

        $this->createProductXmlToProductBuilderLocatorInstance()->createProductBuilderFromXml($xml);
    }

    public function testExceptionIsThrownIfTaxClassIsMissing(): void
    {
        $this->expectException(TaxClassAttributeMissingForImportedProductException::class);
        $xml = '<product sku="foo" type="simple"></product>';

        $this->createProductXmlToProductBuilderLocatorInstance()->createProductBuilderFromXml($xml);
    }

    public function testExceptionIsThrownIfNoFactoryForGivenTypeCodeIsFound(): void
    {
        $this->expectException(NoMatchingProductTypeBuilderFactoryFoundException::class);
        $this->expectExceptionMessage('No product type builder factory for the product type code "invalid" was found');
        $xml = '<product type="invalid" sku="test" tax_class="test"></product>';

        $this->createProductXmlToProductBuilderLocatorInstance()->createProductBuilderFromXml($xml);
    }
}
