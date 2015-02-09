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
	 * @param ProductSource|ProjectionSourceData $product
	 * @param EnvironmentSource $environmentSource
	 * @return null
	 */
	public function project(ProjectionSourceData $product, EnvironmentSource $environmentSource)
	{
		if (!($product instanceof ProductSource)) {
			throw new InvalidProjectionDataSourceType('First argument must be instance of Product.');
		}
		$this->projectProduct($product, $environmentSource);
	}

	/**
	 * @param ProductSource $product
	 * @param EnvironmentSource $environmentSource
	 */
	private function projectProduct(ProductSource $product, EnvironmentSource $environmentSource)
	{
		$snippetResultList = $this->rendererCollection->render($product, $environmentSource);
		$this->dataPoolWriter->writeSnippetResultList($snippetResultList);
	}
}
