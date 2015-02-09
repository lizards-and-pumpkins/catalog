<?php

namespace Brera\Product;

use Brera\SnippetKeyGenerator;
use Brera\InvalidSnippetKeyIdentifierException;
use Brera\Environment\Environment;
use Brera\Environment\VersionedEnvironment;

class HardcodedProductDetailViewSnippetKeyGenerator implements SnippetKeyGenerator
{
	const KEY_PREFIX = 'product_detail_view';

	/**
	 * @param mixed|Product $product
	 * @param Environment $environment
	 * @throws InvalidSnippetKeyIdentifierException
	 * @return string
	 */
	public function getKeyForEnvironment($product, Environment $environment)
	{
		if (!($product instanceof Product)) {
			throw new InvalidSnippetKeyIdentifierException(sprintf(
				'Expected instance of Product, but got "%s"',
				is_scalar($product) ? $product : get_class($product)
			));
		}

		return $this->getKeyForProductIdInEnvironment($product, $environment);
	}

	/**
	 * @param Product $product
	 * @param Environment $environment
	 * @return string
	 */
	private function getKeyForProductIdInEnvironment(Product $product, Environment $environment)
	{
		return sprintf(
			'_%s_%s',
            preg_replace('#[^a-zA-Z0-9]#', '_', $product->getAttributeValue('url_key', $environment)),
			$environment->getValue(VersionedEnvironment::CODE)
        );
	}
}
