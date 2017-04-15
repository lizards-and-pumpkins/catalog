<?php
declare(strict_types=1);

namespace LizardsAndPumpkins\Import\XmlParser;

class ProductJsonToXml
{
    /**
     * @var \XMLWriter
     */
    private $writer;
    /**
     * @var string[]
     */
    private $productNodeAttributes = ['type', 'sku', 'tax_class'];
    /**
     * @var string[]
     */
    private $context = [];

    public function toXml(string $product): string
    {
        $product = json_decode($product, true);

        if (isset($product['context'])) {
            $this->context = $product['context'];
        }

        $this->startDocument();

        $this->writeProducts($product);

        $this->writer->endDocument();
        return $this->writer->outputMemory();
    }

    private function startDocument()
    {
        $this->writer = new \XMLWriter();
        $this->writer->openMemory();
        $this->writer->startDocument('1.0', 'UTF-8');
        $this->writeCatalogRoot();
    }

    private function writeCatalogRoot()
    {
        $this->writer->startElement('catalog');
        $this->writer->writeAttribute('xmlns', 'http://lizardsandpumpkins.com');
        $this->writer->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $this->writer->writeAttribute('xsi:schemaLocation', 'http://lizardsandpumpkins.com ../../schema/catalog.xsd');
        $this->writer->endElement();
    }

    /**
     * @param string[] $product
     */
    private function writeProducts(array $product)
    {
        $this->writer->startElement('products');
        $this->writeProduct($product);
        $this->writer->endElement();
    }

    /**
     * @param string[][] $product
     */
    private function writeProduct(array $product)
    {
        $this->writer->startElement('product');
        foreach ($this->productNodeAttributes as $a) {
            $this->writer->writeAttribute($a, $product[$a]);
        }
        /** @var string[] $attributes */
        $attributes = $product['attributes'];
        foreach ($attributes as $key => $value) {
            $this->writeAttribute($key, $value);
        }
        $this->writer->endElement();
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    private function writeAttribute(string $key, $value)
    {
        if (is_bool($value)) {
            $value = $value ? 'true' : false;
        }
        $this->writer->startElement('attribute');

        $this->writer->writeAttribute('name', $key);
        $this->writeContext();
        $this->writeText($value);

        $this->writer->endElement();
    }

    /**
     * @param mixed $value
     */
    private function writeText($value)
    {
        if (strpos($value, '<') === false && strpos($value, '& ') === false) {
            $this->writer->text($value);
            return;
        }
        $this->writer->writeCData($value);
    }

    private function writeContext()
    {
        foreach ($this->context as $contextKey => $contextValue) {
            $this->writer->writeAttribute($contextKey, $contextValue);
        }
    }
}
