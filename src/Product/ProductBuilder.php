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

		$attributeList = new ProductAttributeList();
		$attributeNodeList = $parser->getXPathNode('product[1]/attributes/attribute');
		foreach ($attributeNodeList as $attributeNode) {
			$attribute = ProductAttribute::fromDomElement($attributeNode);
			$attributeList->add($attribute);
		}

		return new Product($productId, $attributeList);
	}
}
