<?php

namespace Brera\Product;

use Brera\KeyValue\DataPoolWriter;
use Brera\Projector;
use Brera\ProjectionSourceData;
use Brera\Environment\EnvironmentSource;
use Brera\InvalidProjectionDataSourceType;

class ProductProjector implements Projector
{
	/**
	 * @var ProductSnippetRendererCollection
	 */
	private $rendererCollection;

	/**
	 * @var DataPoolWriter
	 */
	private $dataPoolWriter;

	/**
	 * @param ProductSnippetRendererCollection $rendererCollection
	 * @param DataPoolWriter $dataPoolWriter
	 */
	public function __construct(ProductSnippetRendererCollection $rendererCollection, DataPoolWriter $dataPoolWriter)
	{
		$this->rendererCollection = $rendererCollection;
		$this->dataPoolWriter = $dataPoolWriter;
	}

	/**
	 * @param Product|ProjectionSourceData $product
	 * @param EnvironmentSource $environmentSource
	 * @return null
	 */
	public function project(ProjectionSourceData $product, EnvironmentSource $environmentSource)
	{
		if (!($product instanceof Product)) {
			throw new InvalidProjectionDataSourceType('First argument must be instance of Product.');
		}
		$this->projectProduct($product, $environmentSource);
	}

	/**
	 * @param Product $product
	 * @param EnvironmentSource $environmentSource
	 */
	private function projectProduct(Product $product, EnvironmentSource $environmentSource)
	{
		$snippetResultList = $this->rendererCollection->render($product, $environmentSource);
		$this->dataPoolWriter->writeSnippetResultList($snippetResultList);
	}
}
