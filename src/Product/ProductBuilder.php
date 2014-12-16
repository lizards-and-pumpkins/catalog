<?php

namespace Brera\PoC\Product;

use Brera\PoC\PoCDomParser;

class ProductBuilder
{
	/**
	 * @param string $xml
	 * @return Product
	 */
	public function createProductFromXml($xml)
	{
		$name = '';
		$parser = new PoCDomParser($xml);

		if ($productNode = $parser->getXPathNode('product', null, true)) {
			$sku = new PoCSku($productNode->getAttribute('sku'));
			$productId = ProductId::fromSku($sku);
			if ($nameNode = $parser->getXPathNode('attributes/attribute[@code="name"]', $productNode, true)) {
				$name = $nameNode->nodeValue;
			}
		}

		if (empty($productId) || empty($name)) {
			throw new InvalidImportDataException();
		}

		return new Product($productId, $name);
	}
}
