<?php

namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Projection\Catalog\Import\Exception\InvalidNumberOfSkusForImportedProductException;
use LizardsAndPumpkins\Product\ProductAttribute;
use LizardsAndPumpkins\Projection\Catalog\Import\Exception\InvalidProductTypeCodeForImportedProductException;
use LizardsAndPumpkins\Projection\Catalog\Import\Exception\InvalidProductTypeFactoryMethodException;
use LizardsAndPumpkins\Utils\XPathParser;

/**
 * @covers \LizardsAndPumpkins\Projection\Catalog\Import\ProductXmlToProductBuilder
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\SimpleProductBuilder
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\ConfigurableProductBuilder
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\ProductAttributeListBuilder
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\ProductImageListBuilder
 * @uses   \LizardsAndPumpkins\Projection\Catalog\Import\ProductImageBuilder
 * @uses   \LizardsAndPumpkins\Product\ProductId
 * @uses   \LizardsAndPumpkins\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 * @uses   \LizardsAndPumpkins\Product\ProductTypeCode
 * @uses   \LizardsAndPumpkins\Utils\XPathParser
 */
class ProductXmlToProductBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductXmlToProductBuilder
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
        return $domDocument->getElementsByTagName('special_price')->item(0)->nodeValue;
    }

    protected function setUp()
    {
        $this->xmlToProductBuilder = new ProductXmlToProductBuilder();

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

    public function testCustomProductTypeBuilderIsCreatedFromXml()
    {
        $customProductTypeBuilder = $this->getMock(ProductBuilder::class);
        $customProductTypeBuilderFactory = function (XPathParser $parser) use ($customProductTypeBuilder) {
            return $customProductTypeBuilder;
        };
        $xmlToProductBuilder = new ProductXmlToProductBuilder(['custom' => $customProductTypeBuilderFactory]);
        $result = $xmlToProductBuilder->createProductBuilderFromXml('<product type="custom"></product>');
        $this->assertSame($customProductTypeBuilder, $result);
    }

    public function testProductBuilderIsCreatedFromXmlIgnoringAssociatedProductAttributes()
    {
        $configurableProductXml = $this->getConfigurableProductXml();

        $productBuilder = $this->xmlToProductBuilder->createProductBuilderFromXml($configurableProductXml);
        $simpleProductBuilderDelegate = $this->getPrivatePropertyValue($productBuilder, 'simpleProductBuilderDelegate');
        $sizeAttributes = $this->getAttributesWithCodeFromInstance($simpleProductBuilderDelegate, 'size');
        $this->assertEmpty($sizeAttributes, 'The configurable product builder has "size" attributes');
        $colorAttributes = $this->getAttributesWithCodeFromInstance($simpleProductBuilderDelegate, 'color');
        $this->assertEmpty($colorAttributes, 'The configurable product builder has "color" attributes');
    }

    public function testExceptionIsThrownIfSkuIsMissing()
    {
        $this->setExpectedException(InvalidNumberOfSkusForImportedProductException::class);
        $xml = '<product type="simple"></product>';

        (new ProductXmlToProductBuilder())->createProductBuilderFromXml($xml);
    }

    public function testExceptionIsThrownIfProductTypeCodeIsMissing()
    {
        $this->setExpectedException(InvalidProductTypeCodeForImportedProductException::class);
        $xml = '<product sku="foo"></product>';

        (new ProductXmlToProductBuilder())->createProductBuilderFromXml($xml);
    }

    public function testItThrowsAnExceptionIfANonCallableIsInjected()
    {
        $this->setExpectedException(
            InvalidProductTypeFactoryMethodException::class,
            'Custom product type builder factory methods have to be callable, got "foo"'
        );
        new ProductXmlToProductBuilder(['test' => 'foo']);
    }
}
