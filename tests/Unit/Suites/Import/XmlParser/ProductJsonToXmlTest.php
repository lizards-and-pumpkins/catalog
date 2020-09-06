<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\XmlParser;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\XmlParser\ProductJsonToXml
 */
class ProductJsonToXmlTest extends TestCase
{
    private $sku = '118235-251';

    private $type = 'simple';

    private $taxClass = '19%';

    /**
     * @var ProductJsonToXml
     */
    private $productJsonToXml;

    /**
     * @return string
     */
    private function getProductJson() : string
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

    private function getProductJsonWithContext() : string
    {
        $product = json_decode($this->getProductJson(), true);
        $product['context'] = [
            'website' => 'german',
            'locale'  => 'de_DE',
        ];
        return json_encode($product);
    }

    final protected function setUp(): void
    {
        $this->productJsonToXml = new ProductJsonToXml();
    }

    public function testImplementsProductJsonToXml(): void
    {
        $this->assertInstanceOf(ProductJsonToXml::class, $this->productJsonToXml);
    }

    public function testStartsWithXmlHeader(): void
    {
        $xml = $this->productJsonToXml->toXml($this->getProductJson());
        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $xml);
    }

    public function testContainsClosingProductNode(): void
    {
        $xml = $this->productJsonToXml->toXml($this->getProductJson());
        $this->assertStringEndsWith("</product>\n", $xml);
    }

    public function testWritesProductNodeWithAttributes(): void
    {
        $xml = $this->productJsonToXml->toXml($this->getProductJson());
        $productNode = "<product type=\"{$this->type}\" sku=\"{$this->sku}\" tax_class=\"{$this->taxClass}\"";

        $this->assertStringContainsString($productNode, $xml);
    }

    public function testAttributeNodesAreInsideAttributesNode(): void
    {
        $xml = $this->productJsonToXml->toXml($this->getProductJson());

        $this->assertStringContainsString('<attributes><attribute', $xml);
    }

    public function testIsValidXml(): void
    {
        $simpleXml = simplexml_load_string($xml = $this->productJsonToXml->toXml($this->getProductJson()));
        $this->assertInstanceOf(\SimpleXMLElement::class, $simpleXml);
    }

    public function testWriteAttributes(): void
    {
        $xml = $this->productJsonToXml->toXml($this->getProductJson());

        $this->assertStringContainsString('<attribute name="backorders">true</attribute>', $xml);
        $this->assertStringContainsString('<attribute name="url_key">led-arm-signallampe</attribute>', $xml);
    }

    public function testWriteCData(): void
    {
        $xml = $this->productJsonToXml->toXml($this->getProductJson());
        $this->assertStringContainsString('<attribute name="description"><![CDATA[', $xml);
    }

    public function testWritesContextToAttributes(): void
    {
        $xml = $this->productJsonToXml->toXml($this->getProductJsonWithContext());
        $this->assertStringContainsString('<attribute name="description" website="german" locale="de_DE">', $xml);
        $this->assertStringContainsString('<attribute name="backorders" website="german" locale="de_DE">', $xml);
    }
}
