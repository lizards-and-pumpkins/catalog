<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\Context\ContextBuilder;
use Brera\Product\Exception\MalformedProductListingSourceJsonException;

/**
 * @covers \Brera\Product\ProductListingSourceListBuilder
 * @uses   \Brera\Product\ProductListingSourceList
 * @uses   \Brera\Product\ProductListingSource
 */
class ProductListingSourceListBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListingSourceListBuilder
     */
    private $productListingSourceListBuilder;

    protected function setUp()
    {
        $stubContext = $this->getMock(Context::class);

        /** @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject $mockContextBuilder */
        $mockContextBuilder = $this->getMock(ContextBuilder::class, [], [], '', false);
        $mockContextBuilder->method('createContext')->willReturn($stubContext);

        $this->productListingSourceListBuilder = new ProductListingSourceListBuilder($mockContextBuilder);
    }

    public function testExceptionIsThrownIfProductPerPageElementIsAbsentInJson()
    {
        $this->setExpectedException(MalformedProductListingSourceJsonException::class);
        $this->productListingSourceListBuilder->fromJson('{}');
    }

    public function testExceptionIsThrownIfProductsPerPageInstructionIsNonArray()
    {
        $json = json_encode(['products_per_page' => 1]);
        $this->setExpectedException(MalformedProductListingSourceJsonException::class);
        $this->productListingSourceListBuilder->fromJson($json);
    }

    public function testExceptionIsThrownIfProductsPerPageInstructionIsMissingContextInformation()
    {
        $json = json_encode([
            'products_per_page' => [
                [
                    'number' => 1
                ]
            ]
        ]);
        $this->setExpectedException(MalformedProductListingSourceJsonException::class);
        $this->productListingSourceListBuilder->fromJson($json);
    }

    public function testExceptionIsThrownIfProductsPerPageInstructionContextInformationIsNonArray()
    {
        $json = json_encode([
            'products_per_page' => [
                [
                    'number'  => 1,
                    'context' => 'foo'
                ]
            ]
        ]);
        $this->setExpectedException(MalformedProductListingSourceJsonException::class);
        $this->productListingSourceListBuilder->fromJson($json);
    }

    public function testExceptionIsThrownIfProductsPerPageNumberIsMissing()
    {
        $json = json_encode([
            'products_per_page' => [
                [
                    'context' => []
                ]
            ]
        ]);
        $this->setExpectedException(MalformedProductListingSourceJsonException::class);
        $this->productListingSourceListBuilder->fromJson($json);
    }

    public function testExceptionIsThrownIfProductsPerPageNumberIsNonInteger()
    {
        $json = json_encode([
            'products_per_page' => [
                [
                    'number'  => 1.2,
                    'context' => []
                ]
            ]
        ]);
        $this->setExpectedException(MalformedProductListingSourceJsonException::class);
        $this->productListingSourceListBuilder->fromJson($json);
    }

    public function testProductListingSourceListCanBeCreatedFromJson()
    {
        $json = file_get_contents(__DIR__ . '/../../../shared-fixture/product-listing-root-snippet.json');
        $productListingSourceList = $this->productListingSourceListBuilder->fromJson($json);

        $this->assertInstanceOf(ProductListingSourceList::class, $productListingSourceList);
    }
}
