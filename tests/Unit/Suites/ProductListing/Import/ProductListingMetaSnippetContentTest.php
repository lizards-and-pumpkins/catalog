<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\ProductDetail\ProductDetailPageMetaInfoSnippetContent;
use LizardsAndPumpkins\ProductListing\Import\Exception\MalformedSearchCriteriaMetaException;
use LizardsAndPumpkins\Util\Exception\InvalidSnippetCodeException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\ProductListingMetaSnippetContent
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual
 * @uses   \LizardsAndPumpkins\Import\SnippetContainer
 * @uses   \LizardsAndPumpkins\Util\SnippetCodeValidator
 */
class ProductListingMetaSnippetContentTest extends TestCase
{
    /**
     * @var ProductListingMetaSnippetContent
     */
    private $pageMetaInfo;

    /**
     * @var string
     */
    private $rootSnippetCode = 'root-snippet-code';

    private $containerSnippets = ['additional-info' => []];

    private $pageSpecificData = [['foo' => 'bar'], ['baz' => 'qux']];

    /**
     * @var CompositeSearchCriterion|MockObject
     */
    private $stubSelectionCriteria;

    final protected function setUp(): void
    {
        $this->stubSelectionCriteria = $this->createMock(CompositeSearchCriterion::class);
        $this->stubSelectionCriteria->method('jsonSerialize')->willReturn([
            'condition' => CompositeSearchCriterion::AND_CONDITION,
            'criteria' => [
                [
                    'condition' => CompositeSearchCriterion::AND_CONDITION,
                    'criteria' => [],
                ],
            ],
        ]);

        $pageSnippetCodes = [$this->rootSnippetCode];

        $this->pageMetaInfo = ProductListingMetaSnippetContent::create(
            $this->stubSelectionCriteria,
            $this->rootSnippetCode,
            $pageSnippetCodes,
            $this->containerSnippets,
            $this->pageSpecificData
        );
    }

    public function testReturnsPageMetaSnippetAsArray(): void
    {
        $this->assertIsArray($this->pageMetaInfo->toArray());
    }

    public function testExpectedArrayKeysArePresentInJsonContent(): void
    {
        $keys = [
            ProductListingMetaSnippetContent::KEY_CRITERIA,
            ProductListingMetaSnippetContent::KEY_ROOT_SNIPPET_CODE,
            ProductListingMetaSnippetContent::KEY_PAGE_SNIPPET_CODES,
            ProductListingMetaSnippetContent::KEY_CONTAINER_SNIPPETS,
        ];

        $result = $this->pageMetaInfo->toArray();

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $result, sprintf('Page meta info array is lacking "%s" key', $key));
        }
    }

    public function testExceptionIsThrownIfTheRootSnippetCodeIsAnEmptyString(): void
    {
        $this->expectException(InvalidSnippetCodeException::class);
        ProductListingMetaSnippetContent::create($this->stubSelectionCriteria, '', [], [], []);
    }

    public function testRootSnippetCodeIsAddedToTheSnippetCodeListIfNotPresent(): void
    {
        $rootSnippetCode = 'root-snippet-code';
        $pageMeta = ProductListingMetaSnippetContent::create($this->stubSelectionCriteria, $rootSnippetCode, [], [], []);

        $this->assertTrue(in_array(
            $rootSnippetCode,
            $pageMeta->toArray()[ProductListingMetaSnippetContent::KEY_PAGE_SNIPPET_CODES]
        ));
    }

    public function testJsonConstructorIsPresent(): void
    {
        $pageMeta = json_decode(json_encode($this->pageMetaInfo->toArray()), true);
        $pageMetaSnippet = ProductListingMetaSnippetContent::fromArray($pageMeta);

        $this->assertInstanceOf(ProductListingMetaSnippetContent::class, $pageMetaSnippet);
    }

    /**
     * @dataProvider pageInfoArrayKeyProvider
     * @param string $key
     */
    public function testExceptionIsThrownIfARequiredKeyIsMissing(string $key): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Missing key in input array');
        $pageMeta = $this->pageMetaInfo->toArray();
        unset($pageMeta[$key]);
        ProductListingMetaSnippetContent::fromArray($pageMeta);
    }

    /**
     * @return array[]
     */
    public function pageInfoArrayKeyProvider() : array
    {
        return [
            [ProductListingMetaSnippetContent::KEY_CRITERIA],
            [ProductDetailPageMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE],
            [ProductDetailPageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES],
            [ProductDetailPageMetaInfoSnippetContent::KEY_CONTAINER_SNIPPETS],
        ];
    }

    public function testSelectionCriteriaIsReturned(): void
    {
        $this->assertEquals($this->stubSelectionCriteria, $this->pageMetaInfo->getSelectionCriteria());
    }

    public function testRootSnippetCodeIsReturned(): void
    {
        $this->assertEquals($this->rootSnippetCode, $this->pageMetaInfo->getRootSnippetCode());
    }

    public function testPageSnippetCodeListIsReturned(): void
    {
        $this->assertIsArray($this->pageMetaInfo->getPageSnippetCodes());
    }

    public function testExceptionIsThrownIfSearchCriteriaConditionIsMissing(): void
    {
        $this->expectException(MalformedSearchCriteriaMetaException::class);
        $this->expectExceptionMessage('Missing criteria condition.');

        $pageMeta = [
            ProductListingMetaSnippetContent::KEY_CRITERIA => [],
            ProductListingMetaSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingMetaSnippetContent::KEY_ROOT_SNIPPET_CODE => '',
            ProductListingMetaSnippetContent::KEY_CONTAINER_SNIPPETS => [],
        ];

        ProductListingMetaSnippetContent::fromArray($pageMeta);
    }

    public function testExceptionIsThrownIfSearchCriteriaCriteriaIsMissing(): void
    {
        $this->expectException(MalformedSearchCriteriaMetaException::class);
        $this->expectExceptionMessage('Missing criteria.');

        $pageMeta = [
            ProductListingMetaSnippetContent::KEY_CRITERIA => [
                'condition' => CompositeSearchCriterion::AND_CONDITION,
            ],
            ProductListingMetaSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingMetaSnippetContent::KEY_ROOT_SNIPPET_CODE => '',
            ProductListingMetaSnippetContent::KEY_CONTAINER_SNIPPETS => [],
        ];

        ProductListingMetaSnippetContent::fromArray($pageMeta);
    }

    public function testExceptionIsThrownIfCriterionFieldNameIsMissing(): void
    {
        $this->expectException(MalformedSearchCriteriaMetaException::class);
        $this->expectExceptionMessage('Missing criterion field name.');

        $pageMeta = [
            ProductListingMetaSnippetContent::KEY_CRITERIA => [
                'condition' => CompositeSearchCriterion::AND_CONDITION,
                'criteria' => [[]],
            ],
            ProductListingMetaSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingMetaSnippetContent::KEY_ROOT_SNIPPET_CODE => '',
            ProductListingMetaSnippetContent::KEY_CONTAINER_SNIPPETS => [],
        ];

        ProductListingMetaSnippetContent::fromArray($pageMeta);
    }

    public function testExceptionIsThrownIfCriterionFieldValueIsMissing(): void
    {
        $this->expectException(MalformedSearchCriteriaMetaException::class);
        $this->expectExceptionMessage('Missing criterion field value.');

        $pageMeta = [
            ProductListingMetaSnippetContent::KEY_CRITERIA => [
                'condition' => CompositeSearchCriterion::AND_CONDITION,
                'criteria' => [
                    ['fieldName' => 'foo'],
                ],
            ],
            ProductListingMetaSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingMetaSnippetContent::KEY_ROOT_SNIPPET_CODE => '',
            ProductListingMetaSnippetContent::KEY_CONTAINER_SNIPPETS => [],
        ];

        ProductListingMetaSnippetContent::fromArray($pageMeta);
    }

    public function testExceptionIsThrownIfCriterionOperationIsMissing(): void
    {
        $this->expectException(MalformedSearchCriteriaMetaException::class);
        $this->expectExceptionMessage('Missing criterion operation.');

        $pageMeta = [
            ProductListingMetaSnippetContent::KEY_CRITERIA => [
                'condition' => CompositeSearchCriterion::AND_CONDITION,
                'criteria' => [
                    ['fieldName' => 'foo', 'fieldValue' => 'bar'],
                ],
            ],
            ProductListingMetaSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingMetaSnippetContent::KEY_ROOT_SNIPPET_CODE => '',
            ProductListingMetaSnippetContent::KEY_CONTAINER_SNIPPETS => [],
        ];

        ProductListingMetaSnippetContent::fromArray($pageMeta);
    }

    public function testExceptionIsThrownIfCriterionOperationIsInvalid(): void
    {
        $invalidOperationName = 'baz';

        $this->expectException(MalformedSearchCriteriaMetaException::class);
        $this->expectExceptionMessage(sprintf('Unknown criterion operation "%s"', $invalidOperationName));

        $pageMeta = [
            ProductListingMetaSnippetContent::KEY_CRITERIA => [
                'condition' => CompositeSearchCriterion::AND_CONDITION,
                'criteria' => [
                    ['fieldName' => 'foo', 'fieldValue' => 'bar', 'operation' => $invalidOperationName],
                ],
            ],
            ProductListingMetaSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingMetaSnippetContent::KEY_ROOT_SNIPPET_CODE => '',
            ProductListingMetaSnippetContent::KEY_CONTAINER_SNIPPETS => [],
        ];

        ProductListingMetaSnippetContent::fromArray($pageMeta);
    }

    public function testProductListingIsCreatedWithPassedSearchCriteria(): void
    {
        $fieldName = 'foo';
        $fieldValue = 'bar';
        $operation = 'Equal';

        $pageMeta = [
            ProductListingMetaSnippetContent::KEY_CRITERIA => [
                'condition' => CompositeSearchCriterion::AND_CONDITION,
                'criteria' => [
                    ['fieldName' => $fieldName, 'fieldValue' => $fieldValue, 'operation' => $operation],
                ],
            ],
            ProductListingMetaSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingMetaSnippetContent::KEY_ROOT_SNIPPET_CODE => 'root',
            ProductListingMetaSnippetContent::KEY_CONTAINER_SNIPPETS => [],
            ProductListingMetaSnippetContent::KEY_PAGE_SPECIFIC_DATA => [],
        ];

        $metaSnippetContent = ProductListingMetaSnippetContent::fromArray($pageMeta);
        $result = $metaSnippetContent->getSelectionCriteria();

        $className = preg_replace('/Criteria$/', 'Criterion', SearchCriteria::class) . $operation;
        $expectedCriterion = new $className($fieldName, $fieldValue);
        $expectedCriteria = CompositeSearchCriterion::createAnd($expectedCriterion);

        $this->assertEquals($expectedCriteria, $result);
    }

    public function testItReturnsThePageSnippetContainers(): void
    {
        $this->assertSame($this->containerSnippets, $this->pageMetaInfo->getContainerSnippets());
    }

    public function testReturnsPageSpecificData(): void
    {
        $this->assertSame($this->pageSpecificData, $this->pageMetaInfo->getPageSpecificData());
    }
}
