<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\ProductDetail\ProductDetailPageMetaInfoSnippetContent;
use LizardsAndPumpkins\ProductListing\Import\Exception\MalformedSearchCriteriaMetaException;
use LizardsAndPumpkins\Util\Exception\InvalidSnippetCodeException;
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
     * @var CompositeSearchCriterion|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSelectionCriteria;

    protected function setUp()
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

    public function testReturnsPageMetaSnippetAsArray()
    {
        $this->assertInternalType('array', $this->pageMetaInfo->toArray());
    }

    public function testExpectedArrayKeysArePresentInJsonContent()
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

    public function testExceptionIsThrownIfTheRootSnippetCodeIsAnEmptyString()
    {
        $this->expectException(InvalidSnippetCodeException::class);
        ProductListingMetaSnippetContent::create($this->stubSelectionCriteria, '', [], [], []);
    }

    public function testRootSnippetCodeIsAddedToTheSnippetCodeListIfNotPresent()
    {
        $rootSnippetCode = 'root-snippet-code';
        $pageMeta = ProductListingMetaSnippetContent::create($this->stubSelectionCriteria, $rootSnippetCode, [], [], []);

        $this->assertContains(
            $rootSnippetCode,
            $pageMeta->toArray()[ProductListingMetaSnippetContent::KEY_PAGE_SNIPPET_CODES]
        );
    }

    public function testJsonConstructorIsPresent()
    {
        $pageMetaInfo = ProductListingMetaSnippetContent::fromJson(json_encode($this->pageMetaInfo->toArray()));
        $this->assertInstanceOf(ProductListingMetaSnippetContent::class, $pageMetaInfo);
    }

    public function testExceptionIsThrownInCaseOfJsonErrors()
    {
        $this->expectException(\OutOfBoundsException::class);
        ProductListingMetaSnippetContent::fromJson('malformed-json');
    }

    /**
     * @dataProvider pageInfoArrayKeyProvider
     */
    public function testExceptionIsThrownIfARequiredKeyIsMissing(string $key)
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Missing key in input JSON');
        $pageInfo = $this->pageMetaInfo->toArray();
        unset($pageInfo[$key]);
        ProductListingMetaSnippetContent::fromJson(json_encode($pageInfo));
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

    public function testSelectionCriteriaIsReturned()
    {
        $this->assertEquals($this->stubSelectionCriteria, $this->pageMetaInfo->getSelectionCriteria());
    }

    public function testRootSnippetCodeIsReturned()
    {
        $this->assertEquals($this->rootSnippetCode, $this->pageMetaInfo->getRootSnippetCode());
    }

    public function testPageSnippetCodeListIsReturned()
    {
        $this->assertInternalType('array', $this->pageMetaInfo->getPageSnippetCodes());
    }

    public function testExceptionIsThrownIfSearchCriteriaConditionIsMissing()
    {
        $this->expectException(MalformedSearchCriteriaMetaException::class);
        $this->expectExceptionMessage('Missing criteria condition.');

        $json = json_encode([
            ProductListingMetaSnippetContent::KEY_CRITERIA => [],
            ProductListingMetaSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingMetaSnippetContent::KEY_ROOT_SNIPPET_CODE => '',
            ProductListingMetaSnippetContent::KEY_CONTAINER_SNIPPETS => [],
        ]);

        ProductListingMetaSnippetContent::fromJson($json);
    }

    public function testExceptionIsThrownIfSearchCriteriaCriteriaIsMissing()
    {
        $this->expectException(MalformedSearchCriteriaMetaException::class);
        $this->expectExceptionMessage('Missing criteria.');

        $json = json_encode([
            ProductListingMetaSnippetContent::KEY_CRITERIA => [
                'condition' => CompositeSearchCriterion::AND_CONDITION,
            ],
            ProductListingMetaSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingMetaSnippetContent::KEY_ROOT_SNIPPET_CODE => '',
            ProductListingMetaSnippetContent::KEY_CONTAINER_SNIPPETS => [],
        ]);

        ProductListingMetaSnippetContent::fromJson($json);
    }

    public function testExceptionIsThrownIfCriterionFieldNameIsMissing()
    {
        $this->expectException(MalformedSearchCriteriaMetaException::class);
        $this->expectExceptionMessage('Missing criterion field name.');

        $json = json_encode([
            ProductListingMetaSnippetContent::KEY_CRITERIA => [
                'condition' => CompositeSearchCriterion::AND_CONDITION,
                'criteria' => [[]],
            ],
            ProductListingMetaSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingMetaSnippetContent::KEY_ROOT_SNIPPET_CODE => '',
            ProductListingMetaSnippetContent::KEY_CONTAINER_SNIPPETS => [],
        ]);

        ProductListingMetaSnippetContent::fromJson($json);
    }

    public function testExceptionIsThrownIfCriterionFieldValueIsMissing()
    {
        $this->expectException(MalformedSearchCriteriaMetaException::class);
        $this->expectExceptionMessage('Missing criterion field value.');

        $json = json_encode([
            ProductListingMetaSnippetContent::KEY_CRITERIA => [
                'condition' => CompositeSearchCriterion::AND_CONDITION,
                'criteria' => [
                    ['fieldName' => 'foo'],
                ],
            ],
            ProductListingMetaSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingMetaSnippetContent::KEY_ROOT_SNIPPET_CODE => '',
            ProductListingMetaSnippetContent::KEY_CONTAINER_SNIPPETS => [],
        ]);

        ProductListingMetaSnippetContent::fromJson($json);
    }

    public function testExceptionIsThrownIfCriterionOperationIsMissing()
    {
        $this->expectException(MalformedSearchCriteriaMetaException::class);
        $this->expectExceptionMessage('Missing criterion operation.');

        $json = json_encode([
            ProductListingMetaSnippetContent::KEY_CRITERIA => [
                'condition' => CompositeSearchCriterion::AND_CONDITION,
                'criteria' => [
                    ['fieldName' => 'foo', 'fieldValue' => 'bar'],
                ],
            ],
            ProductListingMetaSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingMetaSnippetContent::KEY_ROOT_SNIPPET_CODE => '',
            ProductListingMetaSnippetContent::KEY_CONTAINER_SNIPPETS => [],
        ]);

        ProductListingMetaSnippetContent::fromJson($json);
    }

    public function testExceptionIsThrownIfCriterionOperationIsInvalid()
    {
        $invalidOperationName = 'baz';

        $this->expectException(MalformedSearchCriteriaMetaException::class);
        $this->expectExceptionMessage(sprintf('Unknown criterion operation "%s"', $invalidOperationName));

        $json = json_encode([
            ProductListingMetaSnippetContent::KEY_CRITERIA => [
                'condition' => CompositeSearchCriterion::AND_CONDITION,
                'criteria' => [
                    ['fieldName' => 'foo', 'fieldValue' => 'bar', 'operation' => $invalidOperationName],
                ],
            ],
            ProductListingMetaSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingMetaSnippetContent::KEY_ROOT_SNIPPET_CODE => '',
            ProductListingMetaSnippetContent::KEY_CONTAINER_SNIPPETS => [],
        ]);

        ProductListingMetaSnippetContent::fromJson($json);
    }

    public function testProductListingIsCreatedWithPassedSearchCriteria()
    {
        $fieldName = 'foo';
        $fieldValue = 'bar';
        $operation = 'Equal';

        $json = json_encode([
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
        ]);

        $metaSnippetContent = ProductListingMetaSnippetContent::fromJson($json);
        $result = $metaSnippetContent->getSelectionCriteria();

        $className = preg_replace('/Criteria$/', 'Criterion', SearchCriteria::class) . $operation;
        $expectedCriterion = new $className($fieldName, $fieldValue);
        $expectedCriteria = CompositeSearchCriterion::createAnd($expectedCriterion);

        $this->assertEquals($expectedCriteria, $result);
    }

    public function testItReturnsThePageSnippetContainers()
    {
        $this->assertSame($this->containerSnippets, $this->pageMetaInfo->getContainerSnippets());
    }
}
