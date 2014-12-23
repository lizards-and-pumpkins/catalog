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
		$parser = new PoCDomParser($xml);

		$skuNode = $parser->getXPathFirstElementOfANode('product[1]/@sku');

		if (!$skuNode || !$skuNode->nodeValue) {
			throw new InvalidImportDataException();
		}

		$sku = PoCSku::fromString($skuNode->nodeValue);
		$productId = ProductId::fromSku($sku);

		$attributeNodeList = $parser->getXPathNode('product[1]/attributes/attribute');
		$attributeList = ProductAttributeList::fromDomNodeList($attributeNodeList);

		return new Product($productId, $attributeList);
	}
}
