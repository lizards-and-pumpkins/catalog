<?php

namespace Brera\Product;

use Brera\DomDocumentXPathParser;

class ProductBuilder
{
	/**
	 * @param string $xml
	 * @return Product
	 */
	public function createProductFromXml($xml)
	{
		$parser = new DomDocumentXPathParser($xml);

		$skuNode = $parser->getXmlNodesArrayByXPath('//product/@sku');
		$skuString = $this->getSkuStringFromDomNodeArray($skuNode);
		$sku = PoCSku::fromString($skuString);
		$productId = ProductId::fromSku($sku);

		$attributeNodes = $parser->getXmlNodesArrayByXPath('//product/attributes/attribute');
		$attributeList = ProductAttributeList::fromArray($attributeNodes);

		return new Product($productId, $attributeList);
	}

	/**
	 * @param array $nodeArray
	 * @return string
	 */
	private function getSkuStringFromDomNodeArray(array $nodeArray)
	{
		if (1 !== count($nodeArray)) {
			throw new InvalidNumberOfSkusPerImportedProductException();
		}

		return $nodeArray[0]['value'];
	}
}
