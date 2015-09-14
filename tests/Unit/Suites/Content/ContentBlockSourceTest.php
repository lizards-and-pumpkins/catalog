<?php

namespace LizardsAndPumpkins\Content;

/**
 * @covers \LizardsAndPumpkins\Content\ContentBlockSource
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
    private $stubContentBlockContent = 'bar';

    /**
     * @var string[]
     */
    private $stubContextData = ['baz' => 'qux'];

    /**
     * @var ContentBlockSource
     */
    private $contentBlockSource;

    protected function setUp()
    {
        $this->stubContentBlockId = $this->getMock(ContentBlockId::class, [], [], '', false);
        $this->contentBlockSource = new ContentBlockSource(
            $this->stubContentBlockId,
            $this->stubContentBlockContent,
            $this->stubContextData
        );
    }

    public function testContentBlockIdIsReturned()
    {
        $result = $this->contentBlockSource->getContentBlockId();
        $this->assertEquals($this->stubContentBlockId, $result);
    }

    public function testContentBlockContentIsReturned()
    {
        $result = $this->contentBlockSource->getContent();
        $this->assertEquals($this->stubContentBlockContent, $result);
    }

    public function testContextDataIsReturned()
    {
        $result = $this->contentBlockSource->getContextData();
        $this->assertEquals($this->stubContextData, $result);
    }
}
