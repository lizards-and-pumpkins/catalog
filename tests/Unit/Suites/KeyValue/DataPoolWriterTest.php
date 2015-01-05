<?php

namespace Brera\KeyValue;

use Brera\SnippetResult;
use Brera\SnippetResultList;

require_once __DIR__ . '/AbstractDataPool.php';

/**
 * @covers \Brera\KeyValue\DataPoolWriter
 * @uses Brera\Product\ProductId
 * @uses Brera\Http\HttpUrl
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
	public function itShouldWriteASnippetListToTheDataPool()
	{
		$testContent = 'test-content';
		$testKey = 'test-key';
		
		$mockSnippetResult = $this->getMockBuilder(SnippetResult::class)
			->disableOriginalConstructor()
			->getMock();
		$mockSnippetResult->expects($this->once())->method('getContent')
			->willReturn($testContent);
		$mockSnippetResult->expects($this->once())->method('getKey')
			->willReturn($testKey);
		
		$mockSnippetResultList = $this->getMock(SnippetResultList::class);
		$mockSnippetResultList->expects($this->once())
			->method('getIterator')
			->willReturn(new \ArrayIterator([$mockSnippetResult]));
		
		$this->stubKeyValueStore->expects($this->once())
			->method('set')
			->with($testKey, $testContent);
		
		$this->dataPoolWriter->writeSnippetResultList($mockSnippetResultList);
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
