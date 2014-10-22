<?php

namespace Brera\PoC\KeyValue;

require_once __DIR__ . '/AbstractDataPool.php';

/**
 * @covers \Brera\PoC\KeyValue\DataPoolWriter
 * @uses Brera\PoC\Product\ProductId
 * @uses Brera\PoC\Http\HttpUrl
 */
class DataPoolWriterTest extends AbstractDataPool
{
	/**
	 * @var DataPoolWriter
	 */
	private $dataPoolWriter;

	protected function setUp()
	{
		parent::setUp();

		$this->dataPoolWriter = new DataPoolWriter($this->stubKeyValueStore, $this->stubKeyGenerator);
	}

	/**
	 * @test
	 */
	public function itShouldStoreProductHtmlWithAProductIdKey()
	{
		$html = '<p>html</p>';
		$productId = $this->getStubProductId();

		$this->addStubMethodToStubKeyGenerator('createPoCProductHtmlKey');
		$this->addSetMethodToStubKeyValueStore();

		$this->dataPoolWriter->setPoCProductHtml($productId, $html);
	}

	/**
	 * @test
	 */
	public function itShouldStoreSeoUrlWithAProductIdKey()
	{
		$productId = $this->getStubProductId();
		$seoUrl = $this->getDummyUrl();

		$this->addStubMethodToStubKeyGenerator('createPoCProductSeoUrlToIdKey');
		$this->addSetMethodToStubKeyValueStore();

		$this->dataPoolWriter->setProductIdBySeoUrl($productId, $seoUrl);
	}
}
