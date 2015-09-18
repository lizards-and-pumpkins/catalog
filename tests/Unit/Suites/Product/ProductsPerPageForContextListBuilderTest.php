<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Context\ContextBuilder;
use LizardsAndPumpkins\Product\Exception\MalformedProductListingSourceJsonException;

/**
 * @covers \LizardsAndPumpkins\Product\ProductsPerPageForContextListBuilder
 * @uses   \LizardsAndPumpkins\Product\ProductsPerPageForContextList
 * @uses   \LizardsAndPumpkins\Product\ProductsPerPageForContext
 */
class ProductsPerPageForContextListBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductsPerPageForContextListBuilder
     */
    private $productsPerPageForContextListBuilder;

    protected function setUp()
    {
        $stubContext = $this->getMock(Context::class);

        /** @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject $mockContextBuilder */
        $mockContextBuilder = $this->getMock(ContextBuilder::class, [], [], '', false);
        $mockContextBuilder->method('createContext')->willReturn($stubContext);

        $this->productsPerPageForContextListBuilder = new ProductsPerPageForContextListBuilder($mockContextBuilder);
    }

    public function testExceptionIsThrownIfProductPerPageElementIsAbsentInJson()
    {
        $this->setExpectedException(MalformedProductListingSourceJsonException::class);
        $this->productsPerPageForContextListBuilder->fromJson('{}');
    }

    public function testExceptionIsThrownIfProductsPerPageInstructionIsNonArray()
    {
        $json = json_encode(['products_per_page' => 1]);
        $this->setExpectedException(MalformedProductListingSourceJsonException::class);
        $this->productsPerPageForContextListBuilder->fromJson($json);
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
        $this->productsPerPageForContextListBuilder->fromJson($json);
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
        $this->productsPerPageForContextListBuilder->fromJson($json);
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
        $this->productsPerPageForContextListBuilder->fromJson($json);
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
        $this->productsPerPageForContextListBuilder->fromJson($json);
    }

    public function testProductsPerPageForContextListCanBeCreatedFromJson()
    {
        $json = file_get_contents(__DIR__ . '/../../../shared-fixture/product-listing-root-snippet.json');
        $productListingSourceList = $this->productsPerPageForContextListBuilder->fromJson($json);

        $this->assertInstanceOf(ProductsPerPageForContextList::class, $productListingSourceList);
    }
}
