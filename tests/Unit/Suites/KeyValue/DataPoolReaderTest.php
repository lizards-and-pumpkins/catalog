<?php

namespace Brera\KeyValue;

use Brera\Product\ProductId;

require_once __DIR__ . '/AbstractDataPool.php';

/**
 * @covers \Brera\KeyValue\DataPoolReader
 * @uses   \Brera\Product\ProductId
 * @uses   \Brera\Http\HttpUrl
 * @uses   \Brera\Product\PoCSku
 */
class DataPoolReaderTest extends AbstractDataPool
{
    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;

    protected function setUp()
    {
        parent::setUp();

        $this->dataPoolReader = new DataPoolReader($this->stubKeyValueStore, $this->stubKeyGenerator);
    }

    /**
     * @test
     */
    public function itShouldReturnASnippetIfItExists()
    {
        $testValue = '<p>html</p>';
        $testKey = 'test';

        $this->addGetMethodToStubKeyValueStore($testValue);

        $this->assertEquals($testValue, $this->dataPoolReader->getSnippet($testKey));
    }

    /**
     * @test
     *
     * @dataProvider snippetListProvider
     */
    public function itShouldReturnASnippetList($keyValueStorageReturn, $expectedList)
    {
        $this->addGetMethodToStubKeyValueStore($keyValueStorageReturn);

        $this->assertEquals(
            $expectedList,
            $this->dataPoolReader->getSnippetList('some_key')
        );
    }

    /**
     * @return array
     */
    public function snippetListProvider()
    {
        return [
            array(
                json_encode(false),
                [],
            ),
            array(
                '[]',
                [],
            ),
            array(
                '{}',
                [],
            ),
            array(
                json_encode(['test_key1', 'test_key2', 'some_key']),
                ['test_key1', 'test_key2', 'some_key']
            ),
        ];
    }

    /**
     * @test
     * @expectedException \RuntimeException
     *
     * @dataProvider brokenJsonProvider
     */
    public function itShouldThrowAnExceptionOnBrokenJSON($keyValueStorageReturn)
    {
        $this->addGetMethodToStubKeyValueStore($keyValueStorageReturn);
        $this->dataPoolReader->getSnippetList('some_key');
    }

    /**
     * @return array
     */
    public function brokenJsonProvider()
    {
        return [
            array(new \stdClass()),
            array([]),
            array('test'),
            array(123),
            array(123.23)
        ];
    }

    /**
     * @test
     * @expectedException \RuntimeException
     *
     * @dataProvider brokenKeyForSnippetListProvider
     */
    public function itShouldOnlyAcceptStringKeyForSnippetList($key)
    {
        $this->dataPoolReader->getSnippetList($key);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     *
     * @dataProvider brokenKeyForSnippetListProvider
     */
    public function itShouldOnlyAcceptStringKeysForGetSnippet($key)
    {
        $this->dataPoolReader->getSnippet($key);
    }

    /**
     * @return array
     */
    public function brokenKeyForSnippetListProvider()
    {
        return [
            array(new \stdClass()),
            array(123),
            array(123.23),
            array([]),
        ];

    }

    public function brokenKeysForSnippetsProvider()
    {
        return [
            array(new \stdClass()),
            array(123),
            array(123.23),
            array('string'),
        ];
    }

    /**
     * @test
     *
     * @expectedException \RuntimeException
     *
     * @dataProvider brokenKeysForSnippetsProvider
     */
    public function itShouldOnlyAcceptStringKeysForGetSnippets($key)
    {
        $this->dataPoolReader->getSnippets($key);
    }

    /**
     * @test
     */
    public function shouldReturnPoCProductHtmlBasedOnKeyFromKeyValueStorage()
    {
        $value = '<p>html</p>';
        $productId = $this->getStubProductId();

        $this->addStubMethodToStubKeyGenerator('createPoCProductHtmlKey');
        $this->addGetMethodToStubKeyValueStore($value);

        $html = $this->dataPoolReader->getPoCProductHtml($productId);

        $this->assertEquals($value, $html);
    }

    /**
     * @test
     */
    public function itShouldReturnProductIdBySeoUrl()
    {
        $value = 'test';
        $url = $this->getDummyUrl();

        $this->addStubMethodToStubKeyGenerator('createPoCProductSeoUrlToIdKey');
        $this->addGetMethodToStubKeyValueStore($value);

        $productId = $this->dataPoolReader->getProductIdBySeoUrl($url);

        $this->assertEquals($value, $productId);
        $this->assertInstanceOf(ProductId::class, $productId);
    }

    /**
     * @test
     */
    public function itShouldReturnIfTheSeoUrlKeyExists()
    {
        $url = $this->getDummyUrl();

        $this->addStubMethodToStubKeyGenerator('createPoCProductSeoUrlToIdKey');
        $this->addHasMethodToStubKeyValueStore(true);

        $this->assertTrue($this->dataPoolReader->hasProductSeoUrl($url));
    }
}
