<?php

namespace Brera;

use Brera\Context\Context;
use Brera\Context\ContextBuilder;

/**
 * @covers \Brera\RootSnippetSourceListBuilder
 * @uses   \Brera\RootSnippetSourceList
 * @uses   \Brera\RootSnippetSource
 */
class RootSnippetSourceListBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RootSnippetSourceListBuilder
     */
    private $rootSnippetSourceListBuilder;

    protected function setUp()
    {
        $stubContext = $this->getMock(Context::class);

        /** @var ContextBuilder|\PHPUnit_Framework_MockObject_MockObject $mockContextBuilder */
        $mockContextBuilder = $this->getMock(ContextBuilder::class, [], [], '', false);
        $mockContextBuilder->method('getContext')->willReturn($stubContext);

        $this->rootSnippetSourceListBuilder = new RootSnippetSourceListBuilder($mockContextBuilder);
    }

    public function testExceptionIsThrownIfProductPerPageElementIsAbsentInJson()
    {
        $this->setExpectedException(MalformedProductListingRootSnippetJsonException::class);
        $this->rootSnippetSourceListBuilder->fromJson('{}');
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
        $this->setExpectedException(MalformedProductListingRootSnippetJsonException::class);
        $this->rootSnippetSourceListBuilder->fromJson($json);
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
        $this->setExpectedException(MalformedProductListingRootSnippetJsonException::class);
        $this->rootSnippetSourceListBuilder->fromJson($json);
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
        $this->setExpectedException(MalformedProductListingRootSnippetJsonException::class);
        $this->rootSnippetSourceListBuilder->fromJson($json);
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
        $this->setExpectedException(MalformedProductListingRootSnippetJsonException::class);
        $this->rootSnippetSourceListBuilder->fromJson($json);
    }

    public function testRootSnippetSourceListCanBeCreatedFromJson()
    {
        $json = file_get_contents(__DIR__ . '/../../shared-fixture/product-listing-root-snippet.json');
        $rootSnippetSourceList = $this->rootSnippetSourceListBuilder->fromJson($json);

        $this->assertInstanceOf(RootSnippetSourceList::class, $rootSnippetSourceList);
    }
}
