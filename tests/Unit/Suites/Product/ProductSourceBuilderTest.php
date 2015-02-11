<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\ProductSourceBuilder
 * @uses   \Brera\Product\ProductSource
 * @uses   \Brera\Product\ProductId
 * @uses   \Brera\Product\PoCSku
 * @uses   \Brera\XPathParser
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

        $xml = file_get_contents(__DIR__ . '/../../../shared-fixture/product.xml');
        $this->domDocument = new \DOMDocument();
        $this->domDocument->loadXML($xml);
    }

    /**
     * @param mixed $expected
     * @param ProductSource $productSource
     * @param string $attributeCode
     */
    private function assertProductAttributeValueEquals($expected, ProductSource $productSource, $attributeCode)
    {
        $property = new \ReflectionProperty($productSource, 'attributes');
        $property->setAccessible(true);
        /** @var ProductAttributeList $attributeList */
        $attributeList = $property->getValue($productSource);
        $this->assertEquals($expected, $attributeList->getAttribute($attributeCode)->getValue());
    }

    /**
     * @test
     */
    public function itShouldCreateAProductSourceFromXml()
    {
        /** @var \DOMElement $firstNode */
        $firstNode = $this->domDocument->getElementsByTagName('product')->item(0);
        $expectedSku = $firstNode->attributes->getNamedItem('sku')->nodeValue;
        $expectedAttribute = $firstNode->getElementsByTagName('special_price')->item(0)->nodeValue;

        $firstNodeXml = $this->domDocument->saveXML($firstNode);

        $productSource = $this->builder->createProductSourceFromXml($firstNodeXml);

        $this->assertInstanceOf(ProductSource::class, $productSource);
        $this->assertEquals($expectedSku, $productSource->getId());
        $this->assertProductAttributeValueEquals($expectedAttribute, $productSource, 'special_price');
    }

    /**
     * @test
     */
    public function itShouldCreateAProductSourceFromXmlIgnoringAssociatedProducts()
    {
        /** @var \DOMElement $secondNode */
        $secondNode = $this->domDocument->getElementsByTagName('product')->item(1);
        $expectedSku = $secondNode->attributes->getNamedItem('sku')->nodeValue;
        $expectedAttribute = $secondNode->getElementsByTagName('price')->item(0)->nodeValue;

        $secondNodeXml = $this->domDocument->saveXML($secondNode);
        $productSource = $this->builder->createProductSourceFromXml($secondNodeXml);

        $this->assertInstanceOf(ProductSource::class, $productSource);
        $this->assertEquals($expectedSku, $productSource->getId());
        $this->assertProductAttributeValueEquals($expectedAttribute, $productSource, 'price');
    }

    /**
     * @test
     * @expectedException \Brera\Product\ProductAttributeNotFoundException
     * @expectedExceptionMessage Can not find an attribute with code "size".
     */
    public function itShouldCreateAProductSourceFromXmlIgnoringAssociatedProductsAttributes()
    {
        /** @var \DOMElement $secondNode */
        $secondNode = $this->domDocument->getElementsByTagName('product')->item(1);
        $secondNodeXml = $this->domDocument->saveXML($secondNode);

        $productSource = $this->builder->createProductSourceFromXml($secondNodeXml);
        $this->assertProductAttributeValueEquals('nothing', $productSource, 'size');
    }

    /**
     * @test
     * @expectedException \Brera\Product\InvalidNumberOfSkusPerImportedProductException
     * @expectedExceptionMessage There must be exactly one SKU in the imported product XML
     */
    public function itShouldThrowAnExceptionInCaseOfXmlHasNoEssentialData()
    {
        $xml = '<?xml version="1.0"?><node />';
        (new ProductSourceBuilder())->createProductSourceFromXml($xml);
    }
}
