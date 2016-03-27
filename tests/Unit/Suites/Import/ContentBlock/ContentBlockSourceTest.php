<?php

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\Import\ContentBlock\ContentBlockId;
use LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource;

/**
 * @covers \LizardsAndPumpkins\Import\ContentBlock\ContentBlockSource
 */
class ContentBlockSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContentBlockId|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContentBlockId;

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
        $this->stubContentBlockId = $this->getMock(ContentBlockId::class, [], [], '', false);
        $this->contentBlockSource = new ContentBlockSource(
            $this->stubContentBlockId,
            $this->testContentBlockContent,
            $this->testContextData,
            $this->testKeyGeneratorParams
        );
    }

    public function testContentBlockIdIsReturned()
    {
        $this->assertEquals($this->stubContentBlockId, $this->contentBlockSource->getContentBlockId());
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
}
