<?php

declare(strict_types=1);

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
    final protected function getMockUrlKeyStore() : UrlKeyStore
    {
        return $this->mockUrlKeyStore;
    }

    /**
     * @return SearchEngine|\PHPUnit_Framework_MockObject_MockObject
     */
    final protected function getMockSearchEngine() : SearchEngine
    {
        return $this->mockSearchEngine;
    }

    /**
     * @return KeyValueStore|\PHPUnit_Framework_MockObject_MockObject
     */
    final protected function getMockKeyValueStore() : KeyValueStore
    {
        return $this->mockKeyValueStore;
    }

    /**
     * @return ProductId|\PHPUnit_Framework_MockObject_MockObject
     */
    final protected function getStubProductId() : ProductId
    {
        return $this->createMock(ProductId::class);
    }

    final protected function addSetMethodToStubKeyValueStore()
    {
        $this->mockKeyValueStore->expects($this->once())->method('set');
    }

    /**
     * @param mixed $returnValue
     */
    final protected function addGetMethodToStubKeyValueStore($returnValue)
    {
        $this->mockKeyValueStore->expects($this->once())->method('get')->willReturn($returnValue);
    }

    /**
     * @param string[] $returnValue
     */
    final protected function addMultiGetMethodToStubKeyValueStore(array $returnValue)
    {
        $this->mockKeyValueStore->expects($this->once())->method('multiGet')->willReturn($returnValue);
    }

    final protected function addHasMethodToStubKeyValueStore(bool $returnResult)
    {
        $this->mockKeyValueStore->expects($this->once())
            ->method('has')
            ->willReturn($returnResult);
    }

    final protected function getDummyUrl() : HttpUrl
    {
        return HttpUrl::fromString('http://example.com/path');
    }
}
