<?php

namespace LizardsAndPumpkins\DataPool;

use LizardsAndPumpkins\DataPool\KeyValueStore\KeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\DataPool\UrlKeyStore\UrlKeyStore;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Import\Product\ProductId;

abstract class AbstractDataPoolTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var KeyValueStore|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockKeyValueStore;

    /**
     * @var SearchEngine|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSearchEngine;

    /**
     * @var UrlKeyStore|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockUrlKeyStore;

    protected function setUp()
    {
        $this->mockKeyValueStore = $this->createMock(KeyValueStore::class);
        $this->mockSearchEngine = $this->createMock(SearchEngine::class);
        $this->mockUrlKeyStore = $this->createMock(UrlKeyStore::class);
    }

    /**
     * @return UrlKeyStore|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockUrlKeyStore()
    {
        return $this->mockUrlKeyStore;
    }

    /**
     * @return SearchEngine|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockSearchEngine()
    {
        return $this->mockSearchEngine;
    }

    /**
     * @return KeyValueStore|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockKeyValueStore()
    {
        return $this->mockKeyValueStore;
    }

    /**
     * @return ProductId|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getStubProductId()
    {
        return $this->createMock(ProductId::class);
    }

    protected function addSetMethodToStubKeyValueStore()
    {
        $this->mockKeyValueStore->expects($this->once())
            ->method('set');
    }

    /**
     * @param string $returnValue
     */
    protected function addGetMethodToStubKeyValueStore($returnValue)
    {
        $this->mockKeyValueStore->expects($this->once())
            ->method('get')
            ->willReturn($returnValue);
    }

    /**
     * @param string[] $returnValue
     */
    protected function addMultiGetMethodToStubKeyValueStore($returnValue)
    {
        $this->mockKeyValueStore->expects($this->once())
            ->method('multiGet')
            ->willReturn($returnValue);
    }

    /**
     * @param boolean $returnResult
     */
    protected function addHasMethodToStubKeyValueStore($returnResult)
    {
        $this->mockKeyValueStore->expects($this->once())
            ->method('has')
            ->willReturn($returnResult);
    }

    /**
     * @return HttpUrl
     */
    protected function getDummyUrl()
    {
        $urlString = 'http://example.com/path';
        $url = HttpUrl::fromString($urlString);

        return $url;
    }
}
