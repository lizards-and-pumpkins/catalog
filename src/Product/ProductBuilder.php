<?php

namespace Brera\Product;

use Brera\XPathParser;

class ProductBuilder
{
	/**
	 * @param string $xml
	 * @return Product
	 */
	public function createProductFromXml($xml)
	{
		$parser = new XPathParser($xml);

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
			throw new InvalidNumberOfSkusPerImportedProductException('There must be exactly one SKU in the imported product XML');
		}

		return $nodeArray[0]['value'];
	}
}
