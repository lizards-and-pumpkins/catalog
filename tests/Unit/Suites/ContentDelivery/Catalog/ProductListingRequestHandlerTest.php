<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\KeyValue\KeyNotFoundException;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\Http\Exception\UnableToHandleRequestException;
use LizardsAndPumpkins\Http\HttpRequest;
use LizardsAndPumpkins\PageBuilder;
use LizardsAndPumpkins\Product\ProductListingCriteriaSnippetContent;
use LizardsAndPumpkins\SnippetKeyGenerator;
use LizardsAndPumpkins\SnippetKeyGeneratorLocator;

/**
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\ProductListingRequestHandler
 * @covers \LizardsAndPumpkins\ContentDelivery\Catalog\ProductListingRequestHandlerTrait
 * @uses   \LizardsAndPumpkins\Product\ProductId
 * @uses   \LizardsAndPumpkins\Product\ProductListingCriteriaSnippetContent
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 */
class ProductListingRequestHandlerTest extends AbstractProductListingRequestHandlerTest
{
    /**
     * @var string
     */
    private $testMetaInfoKey = 'stub-meta-info-key';

    /**
     * @param DataPoolReader|\PHPUnit_Framework_MockObject_MockObject $stubDataPoolReader
     * @return ProductListingRequestHandler
     */
    private function createRequestHandlerWithGivenStubDataPoolReader(DataPoolReader $stubDataPoolReader)
    {
        /** @var Context|\PHPUnit_Framework_MockObject_MockObject $stubContext */
        $stubContext = $this->getMock(Context::class);

        /** @var PageBuilder|\PHPUnit_Framework_MockObject_MockObject $stubPageBuilder */
        $stubPageBuilder = $this->getMock(PageBuilder::class, [], [], '', false);

        $stubSnippetKeyGeneratorLocator = $this->createStubSnippetKeyGeneratorLocator();
        $testFilterNavigationConfig = [];
        $testDefaultNumberOfProductsPerPage = 1;

        return $this->createRequestHandler(
            $stubContext,
            $stubDataPoolReader,
            $stubPageBuilder,
            $stubSnippetKeyGeneratorLocator,
            $testFilterNavigationConfig,
            $testDefaultNumberOfProductsPerPage
        );
    }

    /**
     * @inheritdoc
     */
    final protected function createRequestHandler(
        Context $context,
        DataPoolReader $dataPoolReader,
        PageBuilder $pageBuilder,
        SnippetKeyGeneratorLocator $snippetKeyGeneratorLocator,
        array $filterNavigationConfig,
        $defaultNumberOfProductsPerPage
    ) {
        return new ProductListingRequestHandler(
            $context,
            $dataPoolReader,
            $pageBuilder,
            $snippetKeyGeneratorLocator,
            $filterNavigationConfig,
            $defaultNumberOfProductsPerPage
        );
    }

    /**
     * @inheritdoc
     */
    final protected function createStubSnippetKeyGeneratorLocator()
    {
        $stubMetaInfoSnippetKeyGenerator = $this->getMock(SnippetKeyGenerator::class, [], [], '', false);
        $stubMetaInfoSnippetKeyGenerator->method('getKeyForContext')->willReturn($this->testMetaInfoKey);

        $stubSnippetKeyGeneratorLocator = $this->getMock(SnippetKeyGeneratorLocator::class);
        $stubSnippetKeyGeneratorLocator->method('getKeyGeneratorForSnippetCode')
            ->willReturn($stubMetaInfoSnippetKeyGenerator);

        return $stubSnippetKeyGeneratorLocator;
    }

    /**
     * @inheritdoc
     */
    final protected function createStubDataPoolReader()
    {
        /** @var CompositeSearchCriterion|\PHPUnit_Framework_MockObject_MockObject $stubSelectionCriteria */
        $stubSelectionCriteria = $this->getMock(CompositeSearchCriterion::class, [], [], '', false);
        $stubSelectionCriteria->method('jsonSerialize')
            ->willReturn(['condition' => CompositeSearchCriterion::AND_CONDITION, 'criteria' => []]);

        $pageSnippetCodes = ['child-snippet1'];

        $testMetaInfoSnippetJson = json_encode(ProductListingCriteriaSnippetContent::create(
            $stubSelectionCriteria,
            'root-snippet-code',
            $pageSnippetCodes
        )->getInfo());

        $stubDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);

        $stubDataPoolReader->method('getSnippets')->willReturn([]);
        $stubDataPoolReader->method('getSnippet')->willReturnMap([
            [$this->testMetaInfoKey, $testMetaInfoSnippetJson]
        ]);

        return $stubDataPoolReader;
    }

    /**
     * @inheritdoc
     */
    final protected function createStubRequest()
    {
        return $this->getMock(HttpRequest::class, [], [], '', false);
    }

    public function testFalseIsReturnedIfThePageMetaInfoContentSnippetCanNotBeLoaded()
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubRequest */
        $stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);

        /** @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject $stubDataPoolReader */
        $stubDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);
        $stubDataPoolReader->method('getSnippet')->willThrowException(new KeyNotFoundException);

        $requestHandler = $this->createRequestHandlerWithGivenStubDataPoolReader($stubDataPoolReader);

        $this->assertFalse($requestHandler->canProcess($stubRequest));
    }

    public function testTrueIsReturnedIfThePageMetaInfoContentSnippetCanBeLoaded()
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubRequest */
        $stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);
        $stubDataPoolReader = $this->createStubDataPoolReader();

        $requestHandler = $this->createRequestHandlerWithGivenStubDataPoolReader($stubDataPoolReader);

        $this->assertTrue($requestHandler->canProcess($stubRequest));
    }

    public function testPageMetaInfoIsOnlyLoadedOnce()
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubRequest */
        $stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);

        $mockDataPoolReader = $this->createStubDataPoolReader();
        $mockDataPoolReader->expects($this->once())->method('getSnippet')->with($this->testMetaInfoKey);

        $requestHandler = $this->createRequestHandlerWithGivenStubDataPoolReader($mockDataPoolReader);

        $requestHandler->canProcess($stubRequest);
        $requestHandler->canProcess($stubRequest);
    }

    public function testExceptionIsThrownIfProcessWithoutMetaInfoContentIsCalled()
    {
        /** @var HttpRequest|\PHPUnit_Framework_MockObject_MockObject $stubRequest */
        $stubRequest = $this->getMock(HttpRequest::class, [], [], '', false);

        /** @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject $stubDataPoolReader */
        $stubDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);

        $requestHandler = $this->createRequestHandlerWithGivenStubDataPoolReader($stubDataPoolReader);

        $this->setExpectedException(UnableToHandleRequestException::class);

        $requestHandler->process($stubRequest);
    }
}
