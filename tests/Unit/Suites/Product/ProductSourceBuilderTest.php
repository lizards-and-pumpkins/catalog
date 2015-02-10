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
     * @test
     */
    public function itShouldCreateAProductFromXml()
    {
        /** @var \DOMElement $firstNode */
        $firstNode = $this->domDocument->getElementsByTagName('product')->item(0);
        $expectedSku = $firstNode->attributes->getNamedItem('sku')->nodeValue;
        $expectedAttribute = $firstNode->getElementsByTagName('special_price')->item(0)->nodeValue;

        $firstNodeXml = $this->domDocument->saveXML($firstNode);

        $product = $this->builder->createProductSourceFromXml($firstNodeXml);

        $this->assertInstanceOf(ProductSource::class, $product);
        $this->assertEquals($expectedSku, $product->getId());
        $this->assertEquals($expectedAttribute, $product->getAttributeValue('special_price'));
    }

    /**
     * @test
     */
    public function itShouldCreateAProductFromXmlIgnoringAssociatedProducts()
    {
        /** @var \DOMElement $secondNode */
        $secondNode = $this->domDocument->getElementsByTagName('product')->item(1);
        $expectedSku = $secondNode->attributes->getNamedItem('sku')->nodeValue;
        $expectedAttribute = $secondNode->getElementsByTagName('price')->item(0)->nodeValue;

        $secondNodeXml = $this->domDocument->saveXML($secondNode);
        $product = $this->builder->createProductSourceFromXml($secondNodeXml);

        $this->assertInstanceOf(ProductSource::class, $product);
        $this->assertEquals($expectedSku, $product->getId());
        $this->assertEquals($expectedAttribute, $product->getAttributeValue('price'));
    }

    /**
     * @test
     * @expectedException \Brera\Product\ProductAttributeNotFoundException
     * @expectedExceptionMessage Can not find an attribute with code "size".
     */
    public function itShouldCreateAProductFromXmlIgnoringAssociatedProductsAttributes()
    {
        /** @var \DOMElement $secondNode */
        $secondNode = $this->domDocument->getElementsByTagName('product')->item(1);
        $secondNodeXml = $this->domDocument->saveXML($secondNode);

        $product = $this->builder->createProductSourceFromXml($secondNodeXml);
        $product->getAttributeValue('size');
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
