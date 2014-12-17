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

		if ($productNode = $parser->getXPathFirstElementOfANode('product')) {
			$sku = new PoCSku($productNode->getAttribute('sku'));
			$productId = ProductId::fromSku($sku);
			if ($nameNode = $parser->getXPathFirstElementOfANode('product[1]/attributes/attribute[@code="name"]')) {
				$name = $nameNode->nodeValue;
			}
		}

		if (empty($productId) || empty($name)) {
			throw new InvalidImportDataException();
		}

		return new Product($productId, $name);
	}
}
