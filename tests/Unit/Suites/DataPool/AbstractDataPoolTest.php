<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool;

use LizardsAndPumpkins\DataPool\KeyValueStore\KeyValueStore;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchEngine;
use LizardsAndPumpkins\DataPool\UrlKeyStore\UrlKeyStore;
use LizardsAndPumpkins\Http\HttpUrl;
use LizardsAndPumpkins\Import\Product\ProductId;
use PHPUnit\Framework\TestCase;

abstract class AbstractDataPoolTest extends TestCase
{
    /**
     * @var KeyValueStore|MockObject
     */
    private $mockKeyValueStore;

    /**
     * @var SearchEngine|MockObject
     */
    private $mockSearchEngine;

    /**
     * @var UrlKeyStore|MockObject
     */
    private $mockUrlKeyStore;

    protected function setUp(): void
    {
        $this->mockKeyValueStore = $this->createMock(KeyValueStore::class);
        $this->mockSearchEngine = $this->createMock(SearchEngine::class);
        $this->mockUrlKeyStore = $this->createMock(UrlKeyStore::class);
    }

    /**
     * @return UrlKeyStore|MockObject
     */
    final protected function getMockUrlKeyStore() : UrlKeyStore
    {
        return $this->mockUrlKeyStore;
    }

    /**
     * @return SearchEngine|MockObject
     */
    final protected function getMockSearchEngine() : SearchEngine
    {
        return $this->mockSearchEngine;
    }

    /**
     * @return KeyValueStore|MockObject
     */
    final protected function getMockKeyValueStore() : KeyValueStore
    {
        return $this->mockKeyValueStore;
    }

    /**
     * @return ProductId|MockObject
     */
    final protected function getStubProductId() : ProductId
    {
        return $this->createMock(ProductId::class);
    }

    final protected function addSetMethodToStubKeyValueStore(): void
    {
        $this->mockKeyValueStore->expects($this->once())->method('set');
    }

    /**
     * @param mixed $returnValue
     */
    final protected function addGetMethodToStubKeyValueStore($returnValue): void
    {
        $this->mockKeyValueStore->expects($this->once())->method('get')->willReturn($returnValue);
    }

    /**
     * @param string[] $returnValue
     */
    final protected function addMultiGetMethodToStubKeyValueStore(array $returnValue): void
    {
        $this->mockKeyValueStore->expects($this->once())->method('multiGet')->willReturn($returnValue);
    }

    final protected function addHasMethodToStubKeyValueStore(bool $returnResult): void
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
