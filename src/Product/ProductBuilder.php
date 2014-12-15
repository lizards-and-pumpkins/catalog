<?php

namespace Brera\PoC\Product;

class ProductBuilder
{
	/**
	 * @param string $xml
	 * @return Product
	 */
	public function createProductFromXml($xml)
	{
		$name = '';

		libxml_clear_errors();
		$internal = libxml_use_internal_errors(true);

		$document = new \DOMDocument;
		$document->preserveWhiteSpace = false;
		$document->loadXML($xml);

		if (!empty(libxml_get_errors())) {
			throw new InvalidImportDataException();
		}

		libxml_use_internal_errors($internal);

		$xpath = new \DOMXPath($document);
		$xpath->registerNamespace('p', 'http://brera.io');

		/** @var \DOMElement $productNode */
		if ($productNode = $xpath->query('p:product')->item(0)) {
			$sku = new PoCSku($productNode->getAttribute('sku'));
			$productId = ProductId::fromSku($sku);
			if ($nameNode = $xpath->query('p:attributes/p:attribute[@code="name"]', $productNode)->item(0)) {
				$name = $nameNode->nodeValue;
			}
		}

		if (empty($productId) || empty($name)) {
			throw new InvalidImportDataException();
		}

		return new Product($productId, $name);
	}
}
