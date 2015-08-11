<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductSourceBuilder
 * @uses   \Brera\Product\ProductSource
 * @uses   \Brera\Product\ProductId
 * @uses   \Brera\Product\SampleSku
 * @uses   \Brera\Utils\XPathParser
 * @uses   \Brera\Product\ProductAttribute
 * @uses   \Brera\Product\ProductAttributeList
 */
class ProductSourceBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductSourceBuilder
     */
    private $builder;

    /**
     * @var \DOMDocument
     */
    private $domDocument;

    protected function setUp()
    {
        $this->builder = new ProductSourceBuilder();

        $xml = file_get_contents(__DIR__ . '/../../../shared-fixture/catalog.xml');
        $this->domDocument = new \DOMDocument();
        $this->domDocument->loadXML($xml);
    }

    public function testProductSourceIsCreatedFromXml()
    {
        /** @var \DOMElement $firstNode */
        $firstNode = $this->domDocument->getElementsByTagName('product')->item(0);
        $expectedSku = $firstNode->attributes->getNamedItem('sku')->nodeValue;
        $expectedAttribute = $firstNode->getElementsByTagName('special_price')->item(0)->nodeValue;

        $firstNodeXml = $this->domDocument->saveXML($firstNode);

        $productSource = $this->builder->createProductSourceFromXml($firstNodeXml);

        $this->assertInstanceOf(ProductSource::class, $productSource);
        $this->assertEquals($expectedSku, $productSource->getId());
        $this->assertFirstProductAttributeInAListValueEquals($expectedAttribute, $productSource, 'special_price');
    }

    public function testProductSourceIsCreatedFromXmlIgnoringAssociatedProducts()
    {
        /** @var \DOMElement $secondNode */
        $secondNode = $this->domDocument->getElementsByTagName('product')->item(1);
        $expectedSku = $secondNode->attributes->getNamedItem('sku')->nodeValue;
        $expectedAttribute = $secondNode->getElementsByTagName('price')->item(0)->nodeValue;

        $secondNodeXml = $this->domDocument->saveXML($secondNode);
        $productSource = $this->builder->createProductSourceFromXml($secondNodeXml);

        $this->assertInstanceOf(ProductSource::class, $productSource);
        $this->assertEquals($expectedSku, $productSource->getId());
        $this->assertFirstProductAttributeInAListValueEquals($expectedAttribute, $productSource, 'price');
    }

    public function testProductSourceIsCreatedFromXmlIgnoringAssociatedProductsAttributes()
    {
        $secondNode = $this->domDocument->getElementsByTagName('product')->item(1);
        $secondNodeXml = $this->domDocument->saveXML($secondNode);

        $this->setExpectedException(
            ProductAttributeNotFoundException::class,
            'Can not find an attribute with code "size".'
        );

        $productSource = $this->builder->createProductSourceFromXml($secondNodeXml);
        $this->assertFirstProductAttributeInAListValueEquals('nothing', $productSource, 'size');
    }

    public function testExceptionIsThrownIfXmlHasNoEssentialData()
    {
        $this->setExpectedException(InvalidNumberOfSkusPerImportedProductException::class);
        (new ProductSourceBuilder())->createProductSourceFromXml('<?xml version="1.0"?><node/>');
    }

    /**
     * @param mixed $expected
     * @param ProductSource $productSource
     * @param string $attributeCode
     */
    private function assertFirstProductAttributeInAListValueEquals($expected, ProductSource $productSource, $attributeCode)
    {
        $property = new \ReflectionProperty($productSource, 'attributes');
        $property->setAccessible(true);
        /** @var ProductAttributeList $attributeList */
        $attributeList = $property->getValue($productSource);
        $this->assertEquals($expected, $attributeList->getAttributesWithCode($attributeCode)[0]->getValue());
    }
}
