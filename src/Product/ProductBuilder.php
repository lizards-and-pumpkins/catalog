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

		$skuNodeList = $parser->getXPathNode('//product/@sku');
		$skuString = $this->getSkuStringFromDomNodeList($skuNodeList);
		$sku = PoCSku::fromString($skuString);
		$productId = ProductId::fromSku($sku);

		$attributeNodeList = $parser->getXPathNode('//product/attributes/attribute');
		$attributeList = ProductAttributeList::fromDomNodeList($attributeNodeList);

		return new Product($productId, $attributeList);
	}

	/**
	 * @param string $xml
	 * @return array
	 */
	public function getProductXmlArray($xml)
	{
		$parser = new PoCDomParser($xml);
		$productNodesArray = [];

		$productNodes = $parser->getXPathNode('product');
		foreach ($productNodes as $productNode) {
			$productNodesArray[] = $parser->getDomNodeXml($productNode);
		}

		return $productNodesArray;
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
