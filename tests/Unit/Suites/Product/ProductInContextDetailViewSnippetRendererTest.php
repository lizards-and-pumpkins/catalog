<?php


namespace Brera\Product;

use Brera\Context\Context;
use Brera\SnippetResultList;
use Brera\TestFileFixtureTrait;

/**
 * @covers \Brera\Product\ProductInContextDetailViewSnippetRenderer
 * @uses   \Brera\SnippetResult
 */
class ProductInContextDetailViewSnippetRendererTest extends \PHPUnit_Framework_TestCase
{
    use TestFileFixtureTrait;

    /**
     * @var ProductInContextDetailViewSnippetRenderer
     */
    private $renderer;

    /**
     * @var SnippetResultList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockSnippetResultList;

    /**
     * @var ProductDetailViewBlockRenderer
     */
    private $stubProductDetailViewBlockRenderer;

    /**
     * @var ProductDetailViewSnippetKeyGenerator
     */
    private $stubProductDetailViewSnippetKeyGenerator;

    protected function setUp()
    {
        $this->mockSnippetResultList = $this->getMock(SnippetResultList::class);
        $this->stubProductDetailViewBlockRenderer = $this->getMockBuilder(ProductDetailViewBlockRenderer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->stubProductDetailViewBlockRenderer->expects($this->any())
            ->method('render')
            ->willReturn('dummy content');
        $this->stubProductDetailViewBlockRenderer->expects($this->any())
            ->method('getNestedSnippetCodes')
            ->willReturn([]);
        $this->stubProductDetailViewSnippetKeyGenerator = $this->getMock(ProductDetailViewSnippetKeyGenerator::class);
        $this->stubProductDetailViewSnippetKeyGenerator->expects($this->any())
            ->method('getKeyForContext')
            ->willReturn('stub-content-key');
        $this->stubProductDetailViewSnippetKeyGenerator->expects($this->any())
            ->method('getUrlKeyForPathInContext')
            ->willReturn('stub-url-key');
        $this->renderer = new ProductInContextDetailViewSnippetRenderer(
            $this->mockSnippetResultList,
            $this->stubProductDetailViewBlockRenderer,
            $this->stubProductDetailViewSnippetKeyGenerator
        );
    }

    /**
     * @test
     */
    public function itShouldRenderProductDetailViewSnippets()
    {
        $this->mockSnippetResultList->expects($this->exactly(2))->method('add');
        $stubProduct = $this->getMock(Product::class, [], [], '', false);
        $stubContext = $this->getMock(Context::class, [], [], '', false);
        $this->renderer->render($stubProduct, $stubContext);
    }

    /**
     * @test
     */
    public function itShouldBuildThePageMetadataArray()
    {
        $stubProduct = $this->getMock(Product::class, [], [], '', false);
        $stubContext = $this->getMock(Context::class, [], [], '', false);
        $this->renderer->render($stubProduct, $stubContext);

        $method = new \ReflectionMethod($this->renderer, 'getPageMetaData');
        $method->setAccessible(true);
        $result = $method->invoke($this->renderer);
        $this->assertInternalType('array', $result);
        $this->assertCount(3, $result);
        foreach (['source_id', 'root_snippet_key', 'page_snippet_keys'] as $index) {
            $this->assertTrue(
                array_key_exists('source_id', $result),
                sprintf('The expected page meta data item "%s" is not set', $index)
            );
        }
    }
}
