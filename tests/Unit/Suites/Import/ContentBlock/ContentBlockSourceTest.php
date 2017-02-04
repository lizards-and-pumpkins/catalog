<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\SelfContainedContextBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource
 * @uses   \LizardsAndPumpkins\Import\ContentBlock\ContentBlockId
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContext
 * @uses   \LizardsAndPumpkins\Context\SelfContainedContextBuilder
 */
class ContentBlockSourceTest extends TestCase
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
     * @var Context
     */
    private $testContext;

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
        $this->testContext = SelfContainedContextBuilder::rehydrateContext(['baz' => 'qux']);
        $this->contentBlockSource = new ContentBlockSource(
            $this->testContentBlockId,
            $this->testContentBlockContent,
            $this->testContext,
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

    public function testContextIsReturned()
    {
        $this->assertSame($this->testContext, $this->contentBlockSource->getContext());
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
