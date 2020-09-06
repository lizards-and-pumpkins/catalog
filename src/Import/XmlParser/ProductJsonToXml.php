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
    private $productRootNodeAttributes = ['type', 'sku', 'tax_class'];

    /**
     * @var string[]
     */
    private $context = [];

    public function toXml(string $productDataJson): string
    {
        $productData = json_decode($productDataJson, true);

        if (isset($productData['context'])) {
            $this->context = $productData['context'];
        }

        $this->startDocument();

        $this->writeProductNode($productData);

        $this->writer->endDocument();
        return $this->writer->outputMemory();
    }

    private function startDocument(): void
    {
        $this->writer = new \XMLWriter();
        $this->writer->openMemory();
        $this->writer->startDocument('1.0', 'UTF-8');

    }

    /**
     * @param array[] $productData
     */
    private function writeProductNode(array $productData): void
    {
        $this->writer->startElement('product');
        every($this->productRootNodeAttributes, function (string $attribute) use ($productData) {
            $this->writer->writeAttribute($attribute, $productData[$attribute]);
        });

        $this->writeProductAttributeNodes($productData);
        $this->writer->endElement();
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    private function writeAttributeNode(string $key, $value): void
    {
        if (is_bool($value)) {
            $value = $value ? 'true' : 'false';
        }
        $this->writer->startElement('attribute');

        $this->writer->writeAttribute('name', $key);
        $this->writeContextNode();
        $this->writeTextNode($value);

        $this->writer->endElement();
    }

    private function writeContextNode(): void
    {
        foreach ($this->context as $contextKey => $contextValue) {
            $this->writer->writeAttribute($contextKey, $contextValue);
        }
    }

    /**
     * @param mixed $value
     */
    private function writeTextNode($value): void
    {
        if ($this->containsTriangularBracket($value) || $this->containsAmpersand($value)) {
            $this->writer->writeCData($value);
            return;
        }
        $this->writer->text($value);
    }

    /**
     * @param array[] $product
     */
    private function writeProductAttributeNodes(array $product): void
    {
        $this->writer->startElement('attributes');

        $attributes = $product['attributes'];
        foreach ($attributes as $key => $value) {
            $this->writeAttributeNode($key, $value);
        }
        $this->writer->endElement();
    }

    private function containsTriangularBracket(string $string): bool
    {
        return strpos($string, '<') !== false;
    }

    private function containsAmpersand(string $string): bool
    {
        return strpos($string, '& ') !== false;
    }
}
