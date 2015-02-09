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
	 * @param ProjectionSourceData $productSource
	 * @param EnvironmentSource $environmentSource
	 * @return SnippetResultList
	 */
	final public function render(ProjectionSourceData $productSource, EnvironmentSource $environmentSource)
	{
		if (!($productSource instanceof ProductSource)) {
			throw new InvalidProjectionDataSourceType('First argument must be instance of Product.');
		}

		return $this->renderProduct($productSource, $environmentSource);
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
	 * @param ProductSource $productSource
	 * @param EnvironmentSource $environmentSource
	 * @return SnippetResultList
	 */
	private function renderProduct(ProductSource $productSource, EnvironmentSource $environmentSource)
	{
		$snippetResultList = $this->getSnippetResultList();
		if ($rendererList = $this->getSnippetRenderers()) {
			foreach ($rendererList as $renderer) {
				$snippetResultList->merge($renderer->render($productSource, $environmentSource));
			}
		}

		return $snippetResultList;
	}
}
