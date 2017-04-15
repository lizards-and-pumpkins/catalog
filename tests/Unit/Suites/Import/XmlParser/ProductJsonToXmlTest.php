<?php
declare(strict_types=1);

namespace LizardsAndPumpkins\Import\XmlParser;

use PHPUnit\Framework\TestCase;

class ProductJsonToXmlTest extends TestCase
{
    private $sku = '118235-251';
    private $type = 'simple';
    private $taxClass = '19%';
    /**
     * @var ProductJsonToXml
     */
    private $productJsonToXml;

    public function testImplementsProductJsonToXml()
    {
        $this->assertInstanceOf(ProductJsonToXml::class, $this->productJsonToXml);
    }

    public function testStartsWithXmlHeader()
    {
        $xml = $this->productJsonToXml->toXml($this->getProductJson());
        $this->assertContains('<?xml version="1.0" encoding="UTF-8"?>', $xml);
    }

    /**
     * @return string
     */
    private function getProductJson(): string
    {
        return json_encode([
            'sku'        => $this->sku,
            'type'       => $this->type,
            'tax_class'  => $this->taxClass,
            'attributes' => [
                'backorders'  => true,
                'url_key'     => 'led-arm-signallampe',
                'description' => 'LED Arm-Signallampe<br />
<br />
LED Arm-Signallampe mit elastischem Band und Flasher mit variabler Blinkfolge,
Flasher abnehmbar.',
            ],
        ]);
    }

    public function testContainsCatalogRoot()
    {
        $xml = $this->productJsonToXml->toXml($this->getProductJson());
        $rootNode = '<catalog xmlns="http://lizardsandpumpkins.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://lizardsandpumpkins.com ../../schema/catalog.xsd"';

        $this->assertContains($rootNode, $xml);
    }

    public function testWritesProductNodeWithAttributes()
    {
        $xml = $this->productJsonToXml->toXml($this->getProductJson());
        $productNode = "<product type=\"{$this->type}\" sku=\"{$this->sku}\" tax_class=\"{$this->taxClass}\"";

        $this->assertContains($productNode, $xml);
    }

    public function testWriteAttributes()
    {
        $xml = $this->productJsonToXml->toXml($this->getProductJson());

        $this->assertContains('<attribute name="backorders">true</attribute>', $xml);
        $this->assertContains('<attribute name="url_key">led-arm-signallampe</attribute>', $xml);
    }

    public function testWriteCData()
    {
        $xml = $this->productJsonToXml->toXml($this->getProductJson());
        $this->assertContains('<attribute name="description"><![CDATA[', $xml);
    }

    public function testWritesContextToAttributes()
    {
        $xml = $this->productJsonToXml->toXml($this->getProductJsonWithContext());
        $this->assertContains('<attribute name="description" website="german" locale="de_DE">', $xml);
        $this->assertContains('<attribute name="backorders" website="german" locale="de_DE">', $xml);
    }

    private function getProductJsonWithContext()
    {
        $product = json_decode($this->getProductJson(), true);
        $product['context'] = [
            'website' => 'german',
            'locale'  => 'de_DE',
        ];
        return json_encode($product);
    }

    protected function setUp()
    {
        $this->productJsonToXml = new ProductJsonToXml();
    }
}
