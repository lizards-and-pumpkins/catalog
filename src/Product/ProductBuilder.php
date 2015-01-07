<?php

namespace Brera\Product;

use Brera\PoCDomParser;

class ProductBuilder
{
	/**
	 * @param string $xml
	 * @return Product
	 */
	public function createProductFromXml($xml)
	{
		$parser = new PoCDomParser($xml);

		$skuNodeList = $parser->getXPathNode('//product/@sku');
		$skuString = $this->getSkuStringFromDomNodeList($skuNodeList);
		$sku = PoCSku::fromString($skuString);
		$productId = ProductId::fromSku($sku);

		$attributeNodeList = $parser->getXPathNode('//product/attributes/attribute');
		$attributeList = ProductAttributeList::fromDomNodeList($attributeNodeList);

		return new Product($productId, $attributeList);
	}

	/**
	 * @param \DOMNodeList $nodeList
	 * @return string
	 */
	private function getSkuStringFromDomNodeList(\DOMNodeList $nodeList)
	{
		if (1 !== $nodeList->length) {
			throw new InvalidNumberOfSkusPerImportedProductException();
		}

		return $nodeList->item(0)->nodeValue;
	}
}
