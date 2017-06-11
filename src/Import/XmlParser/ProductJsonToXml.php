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

        $this->writeProduct($product);

        $this->writer->endDocument();
        return $this->writer->outputMemory();
    }

    private function startDocument()
    {
        $this->writer = new \XMLWriter();
        $this->writer->openMemory();
        $this->writer->startDocument('1.0', 'UTF-8');

    }

    /**
     * @param array[] $product
     */
    private function writeProduct(array $product)
    {
        $this->writer->startElement('product');
        foreach ($this->productNodeAttributes as $a) {
            $this->writer->writeAttribute($a, $product[$a]);
        }

        $this->writeAttributes($product);
        $this->writer->endElement();
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    private function writeAttribute(string $key, $value)
    {
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }
        $this->writer->startElement('attribute');

        $this->writer->writeAttribute('name', $key);
        $this->writeContext();
        $this->writeText($value);

        $this->writer->endElement();
    }

    private function writeContext()
    {
        foreach ($this->context as $contextKey => $contextValue) {
            $this->writer->writeAttribute($contextKey, $contextValue);
        }
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

    /**
     * @param array[] $product
     */
    private function writeAttributes(array $product)
    {
        $this->writer->startElement('attributes');

        /** @var string[] $attributes */
        $attributes = $product['attributes'];
        foreach ($attributes as $key => $value) {
            $this->writeAttribute($key, $value);
        }
        $this->writer->endElement();
    }
}
