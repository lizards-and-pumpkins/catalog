<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\UrlKey;

use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Product\UrlKey\UrlKeyForContextCollection
 */
class UrlKeyForContextCollectionTest extends TestCase
{
    /**
     * @var UrlKeyForContext|MockObject
     */
    private $stubUrlKeyForContext;

    final protected function setUp(): void
    {
        $this->stubUrlKeyForContext = $this->createMock(UrlKeyForContext::class);
    }
    
    public function testItIsCountable(): void
    {
        $this->assertInstanceOf(\Countable::class, new UrlKeyForContextCollection());
    }

    public function testItIsAnIteratorAggregate(): void
    {
        $this->assertInstanceOf(\IteratorAggregate::class, new UrlKeyForContextCollection());
    }

    public function testItCountsTheNumberOfUrlKeys(): void
    {
        $this->assertCount(0, new UrlKeyForContextCollection());
        $this->assertCount(1, new UrlKeyForContextCollection($this->stubUrlKeyForContext));
        $this->assertCount(2, new UrlKeyForContextCollection($this->stubUrlKeyForContext, $this->stubUrlKeyForContext));
    }

    public function testItIteratesOverTheGivenUrlKeys(): void
    {
        $collection = new UrlKeyForContextCollection($this->stubUrlKeyForContext);
        foreach ($collection as $urlKey) {
            $this->assertSame($urlKey, $this->stubUrlKeyForContext);
        }
        $this->assertTrue(isset($urlKey), 'No iteration was performed');
    }

    public function testItReturnsAnArrayOfUrlKeys(): void
    {
        $collection = new UrlKeyForContextCollection($this->stubUrlKeyForContext, $this->stubUrlKeyForContext);
        $this->assertSame([$this->stubUrlKeyForContext, $this->stubUrlKeyForContext], $collection->getUrlKeys());
    }
}
