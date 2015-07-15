<?php

namespace Brera\Content;

use Brera\ProjectionSourceData;

/**
 * @covers \Brera\Content\ContentBlockSource
 */
class ContentBlockSourceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $stubContentBlockIdentifier = 'foo';

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
        $this->contentBlockSource = new ContentBlockSource(
            $this->stubContentBlockIdentifier,
            $this->stubContentBlockContent,
            $this->stubContextData
        );
    }

    public function testProjectionSourceDataInterfaceIsImplemented()
    {
        $this->assertInstanceOf(ProjectionSourceData::class, $this->contentBlockSource);
    }

    public function testContentBlockIdentifierIsReturned()
    {
        $result = $this->contentBlockSource->getIdentifier();
        $this->assertEquals($this->stubContentBlockIdentifier, $result);
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
