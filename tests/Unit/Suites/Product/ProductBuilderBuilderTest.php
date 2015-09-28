<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Exception\InvalidNumberOfSkusPerImportedProductException;
use LizardsAndPumpkins\Product\Exception\ProductAttributeNotFoundException;

/**
 * @covers \LizardsAndPumpkins\Product\ProductBuilderBuilder
 * @uses   \LizardsAndPumpkins\Product\ProductBuilder
 * @uses   \LizardsAndPumpkins\Product\ProductId
 * @uses   \LizardsAndPumpkins\Utils\XPathParser
 * @uses   \LizardsAndPumpkins\Product\ProductAttribute
 * @uses   \LizardsAndPumpkins\Product\ProductAttributeListBuilder
 * @uses   \LizardsAndPumpkins\Product\ProductAttributeList
 * @uses   \LizardsAndPumpkins\Product\AttributeCode
 */
class ProductBuilderBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductBuilderBuilder
     */
    private $builder;

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
        $attributeListBuilder = $productBuilder->getAttributeListBuilder();
        $property = new \ReflectionProperty($attributeListBuilder, 'attributes');
        $property->setAccessible(true);
        return $property->getValue($attributeListBuilder);
    }

    protected function setUp()
    {
        $this->builder = new ProductBuilderBuilder();

        $xml = file_get_contents(__DIR__ . '/../../../shared-fixture/catalog.xml');
        $this->domDocument = new \DOMDocument();
        $this->domDocument->loadXML($xml);
    }

    public function testProductBuilderIsCreatedFromXml()
    {
        /** @var \DOMElement $firstNode */
        $firstNode = $this->domDocument->getElementsByTagName('product')->item(0);
        $expectedProductId = $firstNode->attributes->getNamedItem('sku')->nodeValue;
        $expectedAttribute = $firstNode->getElementsByTagName('special_price')->item(0)->nodeValue;

        $firstNodeXml = $this->domDocument->saveXML($firstNode);

        $productBuilder = $this->builder->createProductBuilderFromXml($firstNodeXml);

        $this->assertInstanceOf(ProductBuilder::class, $productBuilder);
        $this->assertEquals($expectedProductId, $productBuilder->getId());
        $this->assertFirstProductAttributeInAListValueEquals($expectedAttribute, $productBuilder, 'special_price');
    }

    public function testProductBuilderIsCreatedFromXmlIgnoringAssociatedProducts()
    {
        /** @var \DOMElement $secondNode */
        $secondNode = $this->domDocument->getElementsByTagName('product')->item(1);
        $expectedSku = $secondNode->attributes->getNamedItem('sku')->nodeValue;
        $expectedAttribute = $secondNode->getElementsByTagName('price')->item(0)->nodeValue;

        $secondNodeXml = $this->domDocument->saveXML($secondNode);
        $productBuilder = $this->builder->createProductBuilderFromXml($secondNodeXml);

        $this->assertInstanceOf(ProductBuilder::class, $productBuilder);
        $this->assertEquals($expectedSku, $productBuilder->getId());
        $this->assertFirstProductAttributeInAListValueEquals($expectedAttribute, $productBuilder, 'price');
    }

    public function testProductBuilderIsCreatedFromXmlIgnoringAssociatedProductsAttributes()
    {
        $secondNode = $this->domDocument->getElementsByTagName('product')->item(1);
        $secondNodeXml = $this->domDocument->saveXML($secondNode);
        
        $productBuilder = $this->builder->createProductBuilderFromXml($secondNodeXml);
        $this->assertEmpty($this->getAttributesWithCodeFromInstance($productBuilder, 'size'));
    }

    public function testExceptionIsThrownIfXmlHasNoEssentialData()
    {
        $this->setExpectedException(InvalidNumberOfSkusPerImportedProductException::class);
        (new ProductBuilderBuilder())->createProductBuilderFromXml('<?xml version="1.0"?><node/>');
    }
}
