<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Import\ContentBlock;

/**
 * @covers \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockId
 */
class ContentBlockSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContentBlockId
     */
    private $testContentBlockId;

    /**
     * @var string
     */
    private $testContentBlockContent = 'bar';

    /**
     * @var string[]
     */
    private $testContextData = ['baz' => 'qux'];

    /**
     * @var mixed[]
     */
    private $testKeyGeneratorParams = ['url_key' => 'foo'];

    /**
     * @var ContentBlockSource
     */
    private $contentBlockSource;

    protected function setUp()
    {
        $this->testContentBlockId = ContentBlockId::fromString('foo');
        $this->contentBlockSource = new ContentBlockSource(
            $this->testContentBlockId,
            $this->testContentBlockContent,
            $this->testContextData,
            $this->testKeyGeneratorParams
        );
    }

    public function testContentBlockIdIsReturned()
    {
        $this->assertEquals($this->testContentBlockId, $this->contentBlockSource->getContentBlockId());
    }

    public function testContentBlockContentIsReturned()
    {
        $this->assertSame($this->testContentBlockContent, $this->contentBlockSource->getContent());
    }

    public function testContextDataIsReturned()
    {
        $this->assertSame($this->testContextData, $this->contentBlockSource->getContextData());
    }

    public function testKeyGeneratorParamsAreReturned()
    {
        $this->assertSame($this->testKeyGeneratorParams, $this->contentBlockSource->getKeyGeneratorParams());
    }

    public function testCanBeSerializedAndRehydrated()
    {
        $rehydrated = ContentBlockSource::rehydrate($this->contentBlockSource->serialize());
        $this->assertEquals($this->contentBlockSource, $rehydrated);
    }
}
