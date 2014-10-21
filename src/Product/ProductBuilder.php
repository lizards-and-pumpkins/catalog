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
		$productId = '';
		$name = '';

		$parser = xml_parser_create();
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, $xml, $values);
		xml_parser_free($parser);

		foreach ($values as $value) {
			if ('sku' === $value['tag']) {
				$sku = new PoCSku($value['value']);
				$productId = ProductId::fromSku($sku);
			}

			if ('name' === $value['tag']) {
				$name = $value['value'];
			}
		}

		if (empty($productId) || empty($name)) {
			throw new InvalidImportDataException();
		}

		return new Product($productId, $name);
	}
}
