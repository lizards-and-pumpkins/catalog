<?php

namespace Brera\KeyValue;

use Brera\Product\ProductId;

/**
 * @covers \Brera\KeyValue\DataPoolReader
 * @uses   \Brera\Product\ProductId
 * @uses   \Brera\Http\HttpUrl
 * @uses   \Brera\Product\PoCSku
 */
class DataPoolReaderTest extends AbstractDataPoolTest
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
            $this->dataPoolReader->getChildSnippetKeys('some_key')
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
    public function itShouldThrowAnExceptionOnBrokenJSON($brokenJson)
    {
        $this->addGetMethodToStubKeyValueStore($brokenJson);
        $this->dataPoolReader->getChildSnippetKeys('some_key');
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
     * @dataProvider invalidKeyProvider
     */
    public function itShouldOnlyAcceptStringKeyForSnippetList($key)
    {
        $this->dataPoolReader->getChildSnippetKeys($key);
    }

    /**
     * @test
     * @expectedException \RuntimeException
     *
     * @dataProvider invalidKeyProvider
     */
    public function itShouldOnlyAcceptStringKeysForGetSnippet($key)
    {
        $this->dataPoolReader->getSnippet($key);
    }

    /**
     * @return array
     */
    public function invalidKeyProvider()
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
    public function itShouldReturnSnippets()
    {
        $keyValueStorageReturn = [
            'key' => 'value',
            'key2' => 'value2',
        ];
        $this->addMultiGetMethodToStubKeyValueStore($keyValueStorageReturn);
        $snippets = $this->dataPoolReader->getSnippets(['key', 'key2']);

        $this->assertEquals($keyValueStorageReturn, $snippets);
    }
}
