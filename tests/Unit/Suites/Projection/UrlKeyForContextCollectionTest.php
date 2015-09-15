<?php


namespace LizardsAndPumpkins\Projection;

/**
 * @covers \LizardsAndPumpkins\Projection\UrlKeyForContextCollection
 */
class UrlKeyForContextCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UrlKeyForContext|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubUrlKeyForContext;

    protected function setUp()
    {
        $this->stubUrlKeyForContext = $this->getMock(UrlKeyForContext::class, [], [], '', false);
    }
    
    public function testItIsCountable()
    {
        $this->assertInstanceOf(\Countable::class, new UrlKeyForContextCollection());
    }

    public function testItIsAnIteratorAggregate()
    {
        $this->assertInstanceOf(\IteratorAggregate::class, new UrlKeyForContextCollection());
    }

    public function testItCountsTheNumberOfUrlKeys()
    {
        $this->assertCount(0, new UrlKeyForContextCollection());
        $this->assertCount(1, new UrlKeyForContextCollection($this->stubUrlKeyForContext));
        $this->assertCount(2, new UrlKeyForContextCollection($this->stubUrlKeyForContext, $this->stubUrlKeyForContext));
    }

    public function testItIteratesOverTheGivenUrlKeys()
    {
        $collection = new UrlKeyForContextCollection($this->stubUrlKeyForContext);
        foreach ($collection as $urlKey) {
            $this->assertSame($urlKey, $this->stubUrlKeyForContext);
        }
        $this->assertTrue(isset($urlKey), 'No iteration was performed');
    }

    public function testItReturnsAnArrayOfUrlKeys()
    {
        $collection = new UrlKeyForContextCollection($this->stubUrlKeyForContext, $this->stubUrlKeyForContext);
        $this->assertSame([$this->stubUrlKeyForContext, $this->stubUrlKeyForContext], $collection->getUrlKeys());
    }
}
