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

		$document = (new \DOMDocument);
		$document->loadXML($xml);

		if (!empty(libxml_get_errors())) {
			throw new InvalidImportDataException();
		}

		libxml_use_internal_errors($internal);

		$xpath = new \DOMXPath($document);

		if ($skuNode = $xpath->query('//product/sku')->item(0)) {
			$sku = new PoCSku($skuNode->nodeValue);
			$productId = ProductId::fromSku($sku);
			if ($nameNode = $xpath->query('//product/name')->item(0)) {
				$name = $nameNode->nodeValue;
			}
		}

		if (empty($productId) || empty($name)) {
			throw new InvalidImportDataException();
		}

		return new Product($productId, $name);
	}
}
