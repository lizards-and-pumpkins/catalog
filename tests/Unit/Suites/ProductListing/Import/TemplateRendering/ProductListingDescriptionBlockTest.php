<?php

namespace LizardsAndPumpkins\ProductListing\Import\TemplateRendering;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\Import\TemplateRendering\Block;
use LizardsAndPumpkins\ProductListing\Import\ProductListing;
use LizardsAndPumpkins\Import\TemplateRendering\BlockRenderer;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\TemplateRendering\ProductListingDescriptionBlock
 * @uses \LizardsAndPumpkins\Import\TemplateRendering\Block
 */
class ProductListingDescriptionBlockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListingDescriptionBlock
     */
    private $productListingDescriptionBlock;

    /**
     * @param string[] $productListingAttributes
     * @return ProductListingDescriptionBlock
     */
    private function createBlockInstance(array $productListingAttributes)
    {
        /** @var BlockRenderer|\PHPUnit_Framework_MockObject_MockObject $stubBlockRenderer */
        $stubBlockRenderer = $this->getMock(BlockRenderer::class, [], [], '', false);
        $productListingDescriptionBlock = new ProductListingDescriptionBlock(
            $stubBlockRenderer,
            'product_listing_description.phtml',
            'product_listing_description',
            $this->createStubProductListingWithAttributes($productListingAttributes)
        );
        return $productListingDescriptionBlock;
    }

    /**
     * @param string[] $attributes
     * @return ProductListing|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubProductListingWithAttributes(array $attributes)
    {
        $stubSearchCriteria = $this->getMock(CompositeSearchCriterion::class, [], [], '', false);
        $stubProductListing = $this->getMock(ProductListing::class, [], [], '', false);
        $stubProductListing->method('getContextData')->willReturn([]);
        $stubProductListing->method('getCriteria')->willReturn($stubSearchCriteria);

        $getAttributeValueMap = $hasAttributeValueMap = [];
        foreach ($attributes as $attributeCode => $attributeValue) {
            $getAttributeValueMap[] = [$attributeCode, $attributeValue];
            $hasAttributeValueMap[] = [$attributeCode, true];
        }
        $hasAttributeValueMap[] = [$this->anything(), false];

        $stubProductListing->method('getAttributeValueByCode')->willReturnMap($getAttributeValueMap);
        $stubProductListing->method('hasAttribute')->willReturnMap($hasAttributeValueMap);

        return $stubProductListing;
    }

    protected function setUp()
    {
        $productListingAttributes = ['title' => 'Test Title', 'description' => 'Test Desc'];
        $this->productListingDescriptionBlock = $this->createBlockInstance($productListingAttributes);
    }

    public function testItIsABlock()
    {
        $this->assertInstanceOf(Block::class, $this->productListingDescriptionBlock);
    }

    public function testItReturnsTheDescription()
    {
        $this->assertSame('Test Desc', $this->productListingDescriptionBlock->getListingDescription());
    }

    public function testItReturnsAnEmptyStringIfNoTitleIsPresent()
    {
        $productListingAttributes = ['description' => 'Test Desc'];
        $productListingDescriptionBlock = $this->createBlockInstance($productListingAttributes);
        $this->assertSame('', $productListingDescriptionBlock->getListingTitle());
    }

    public function testItReturnsTheProductListingTitle()
    {
        $this->assertSame('Test Title', $this->productListingDescriptionBlock->getListingTitle());
    }
}
