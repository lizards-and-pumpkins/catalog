<?php

namespace Brera\Product;

use Brera\Environment\EnvironmentSource;
use Brera\SnippetRendererCollection;
use Brera\ProjectionSourceData;
use Brera\SnippetResultList;
use Brera\InvalidProjectionDataSourceType;
use Brera\SnippetRenderer;

abstract class ProductSnippetRendererCollection implements SnippetRendererCollection
{
	/**
	 * @param ProjectionSourceData $product
	 * @param EnvironmentSource $environmentSource
	 * @return SnippetResultList
	 */
	final public function render(ProjectionSourceData $product, EnvironmentSource $environmentSource)
	{
		if (!($product instanceof Product)) {
			throw new InvalidProjectionDataSourceType('First argument must be instance of Product.');
		}

		return $this->renderProduct($product, $environmentSource);
	}

	/**
	 * @return SnippetResultList
	 */
	abstract protected function getSnippetResultList();

	/**
	 * @return SnippetRenderer[]
	 */
	abstract protected function getSnippetRenderers();

	/**
	 * @param Product $product
	 * @param EnvironmentSource $environment
	 * @return SnippetResultList
	 */
	private function renderProduct(Product $product, EnvironmentSource $environment)
	{
		$snippetResultList = $this->getSnippetResultList();
		if ($rendererList = $this->getSnippetRenderers()) {
			foreach ($rendererList as $renderer) {
				$snippetResultList->merge($renderer->render($product, $environment));
			}
		}

		return $snippetResultList;
	}
}
