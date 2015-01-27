<?php

namespace Brera\Product;

use Brera\Renderer\BlockSnippetRenderer;
use Brera\SnippetResultList;
use Brera\ProjectionSourceData;
use Brera\Environment;
use Brera\SnippetResult;

class ProductDetailViewSnippetRenderer extends BlockSnippetRenderer
{
	const LAYOUT_HANDLE = 'product_details_page';

	/**
	 * @var SnippetResultList
	 */
	private $resultList;

	/**
	 * @var HardcodedProductDetailViewSnippetKeyGenerator
	 */
	private $keyGenerator;

	/**
	 * @param SnippetResultList $resultList
	 * @param HardcodedProductDetailViewSnippetKeyGenerator $keyGenerator
	 */
	public function __construct(
		SnippetResultList $resultList,
		HardcodedProductDetailViewSnippetKeyGenerator $keyGenerator
	) {
		$this->resultList = $resultList;
		$this->keyGenerator = $keyGenerator;
	}

	/**
	 * @param ProjectionSourceData|Product $product
	 * @param Environment $environment
	 * @throws InvalidArgumentException
	 * @return SnippetResultList
	 */
	public function render(ProjectionSourceData $product, Environment $environment)
	{
		if (!($product instanceof Product)) {
			throw new InvalidArgumentException('First argument must be instance of Product.');
		}

		$snippetContent = $this->getSnippetContent('theme/layout/' . self::LAYOUT_HANDLE . '.xml', $product);
		$snippetKey = $this->getKey($product->getId(), $environment);

		$snippet = SnippetResult::create($snippetKey, $snippetContent);
		$this->resultList->add($snippet);

		return $this->resultList;
	}

	/**
	 * @param ProductId $productId
	 * @param Environment $environment
	 * @return string
	 */
	private function getKey(ProductId $productId, Environment $environment)
	{
		return $this->keyGenerator->getKeyForEnvironment($productId, $environment);
	}
}
