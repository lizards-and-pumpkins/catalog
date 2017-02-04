<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\Import;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion;
use LizardsAndPumpkins\ProductDetail\ProductDetailPageMetaInfoSnippetContent;
use LizardsAndPumpkins\ProductListing\Import\Exception\MalformedSearchCriteriaMetaException;
use LizardsAndPumpkins\Util\Exception\InvalidSnippetCodeException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductListing\Import\ProductListingSnippetContent
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual
 * @uses   \LizardsAndPumpkins\Import\SnippetContainer
 * @uses   \LizardsAndPumpkins\Util\SnippetCodeValidator
 */
class ProductListingSnippetContentTest extends TestCase
{
    /**
     * @var ProductListingSnippetContent
     */
    private $pageMetaInfo;

    /**
     * @var string
     */
    private $rootSnippetCode = 'root-snippet-code';

    private $containerSnippets = ['additional-info' => []];

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

        $this->pageMetaInfo = ProductListingSnippetContent::create(
            $this->stubSelectionCriteria,
            $this->rootSnippetCode,
            $pageSnippetCodes,
            $this->containerSnippets
        );
    }

    public function testArrayIsReturned()
    {
        $this->assertInternalType('array', $this->pageMetaInfo->getInfo());
    }

    public function testExpectedArrayKeysArePresentInJsonContent()
    {
        $keys = [
            ProductListingSnippetContent::KEY_CRITERIA,
            ProductListingSnippetContent::KEY_ROOT_SNIPPET_CODE,
            ProductListingSnippetContent::KEY_PAGE_SNIPPET_CODES,
            ProductListingSnippetContent::KEY_CONTAINER_SNIPPETS,
        ];

        $result = $this->pageMetaInfo->getInfo();

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $result, sprintf('Page meta info array is lacking "%s" key', $key));
        }
    }

    public function testExceptionIsThrownIfTheRootSnippetCodeIsAnEmptyString()
    {
        $this->expectException(InvalidSnippetCodeException::class);
        ProductListingSnippetContent::create($this->stubSelectionCriteria, '', [], []);
    }

    public function testRootSnippetCodeIsAddedToTheSnippetCodeListIfNotPresent()
    {
        $rootSnippetCode = 'root-snippet-code';
        $pageMetaInfo = ProductListingSnippetContent::create(
            $this->stubSelectionCriteria,
            $rootSnippetCode,
            [],
            []
        );
        $this->assertContains(
            $rootSnippetCode,
            $pageMetaInfo->getInfo()[ProductListingSnippetContent::KEY_PAGE_SNIPPET_CODES]
        );
    }

    public function testJsonConstructorIsPresent()
    {
        $pageMetaInfo = ProductListingSnippetContent::fromJson(json_encode($this->pageMetaInfo->getInfo()));
        $this->assertInstanceOf(ProductListingSnippetContent::class, $pageMetaInfo);
    }

    public function testExceptionIsThrownInCaseOfJsonErrors()
    {
        $this->expectException(\OutOfBoundsException::class);
        ProductListingSnippetContent::fromJson('malformed-json');
    }

    /**
     * @dataProvider pageInfoArrayKeyProvider
     */
    public function testExceptionIsThrownIfARequiredKeyIsMissing(string $key)
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Missing key in input JSON');
        $pageInfo = $this->pageMetaInfo->getInfo();
        unset($pageInfo[$key]);
        ProductListingSnippetContent::fromJson(json_encode($pageInfo));
    }

    /**
     * @return array[]
     */
    public function pageInfoArrayKeyProvider() : array
    {
        return [
            [ProductListingSnippetContent::KEY_CRITERIA],
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
            ProductListingSnippetContent::KEY_CRITERIA => [],
            ProductListingSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingSnippetContent::KEY_ROOT_SNIPPET_CODE => '',
            ProductListingSnippetContent::KEY_CONTAINER_SNIPPETS => [],
        ]);

        ProductListingSnippetContent::fromJson($json);
    }

    public function testExceptionIsThrownIfSearchCriteriaCriteriaIsMissing()
    {
        $this->expectException(MalformedSearchCriteriaMetaException::class);
        $this->expectExceptionMessage('Missing criteria.');

        $json = json_encode([
            ProductListingSnippetContent::KEY_CRITERIA => [
                'condition' => CompositeSearchCriterion::AND_CONDITION,
            ],
            ProductListingSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingSnippetContent::KEY_ROOT_SNIPPET_CODE => '',
            ProductListingSnippetContent::KEY_CONTAINER_SNIPPETS => [],
        ]);

        ProductListingSnippetContent::fromJson($json);
    }

    public function testExceptionIsThrownIfCriterionFieldNameIsMissing()
    {
        $this->expectException(MalformedSearchCriteriaMetaException::class);
        $this->expectExceptionMessage('Missing criterion field name.');

        $json = json_encode([
            ProductListingSnippetContent::KEY_CRITERIA => [
                'condition' => CompositeSearchCriterion::AND_CONDITION,
                'criteria' => [[]],
            ],
            ProductListingSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingSnippetContent::KEY_ROOT_SNIPPET_CODE => '',
            ProductListingSnippetContent::KEY_CONTAINER_SNIPPETS => [],
        ]);

        ProductListingSnippetContent::fromJson($json);
    }

    public function testExceptionIsThrownIfCriterionFieldValueIsMissing()
    {
        $this->expectException(MalformedSearchCriteriaMetaException::class);
        $this->expectExceptionMessage('Missing criterion field value.');

        $json = json_encode([
            ProductListingSnippetContent::KEY_CRITERIA => [
                'condition' => CompositeSearchCriterion::AND_CONDITION,
                'criteria' => [
                    ['fieldName' => 'foo'],
                ],
            ],
            ProductListingSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingSnippetContent::KEY_ROOT_SNIPPET_CODE => '',
            ProductListingSnippetContent::KEY_CONTAINER_SNIPPETS => [],
        ]);

        ProductListingSnippetContent::fromJson($json);
    }

    public function testExceptionIsThrownIfCriterionOperationIsMissing()
    {
        $this->expectException(MalformedSearchCriteriaMetaException::class);
        $this->expectExceptionMessage('Missing criterion operation.');

        $json = json_encode([
            ProductListingSnippetContent::KEY_CRITERIA => [
                'condition' => CompositeSearchCriterion::AND_CONDITION,
                'criteria' => [
                    ['fieldName' => 'foo', 'fieldValue' => 'bar'],
                ],
            ],
            ProductListingSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingSnippetContent::KEY_ROOT_SNIPPET_CODE => '',
            ProductListingSnippetContent::KEY_CONTAINER_SNIPPETS => [],
        ]);

        ProductListingSnippetContent::fromJson($json);
    }

    public function testExceptionIsThrownIfCriterionOperationIsInvalid()
    {
        $invalidOperationName = 'baz';

        $this->expectException(MalformedSearchCriteriaMetaException::class);
        $this->expectExceptionMessage(sprintf('Unknown criterion operation "%s"', $invalidOperationName));

        $json = json_encode([
            ProductListingSnippetContent::KEY_CRITERIA => [
                'condition' => CompositeSearchCriterion::AND_CONDITION,
                'criteria' => [
                    ['fieldName' => 'foo', 'fieldValue' => 'bar', 'operation' => $invalidOperationName],
                ],
            ],
            ProductListingSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingSnippetContent::KEY_ROOT_SNIPPET_CODE => '',
            ProductListingSnippetContent::KEY_CONTAINER_SNIPPETS => [],
        ]);

        ProductListingSnippetContent::fromJson($json);
    }

    public function testProductListingIsCreatedWithPassedSearchCriteria()
    {
        $fieldName = 'foo';
        $fieldValue = 'bar';
        $operation = 'Equal';

        $json = json_encode([
            ProductListingSnippetContent::KEY_CRITERIA => [
                'condition' => CompositeSearchCriterion::AND_CONDITION,
                'criteria' => [
                    ['fieldName' => $fieldName, 'fieldValue' => $fieldValue, 'operation' => $operation],
                ],
            ],
            ProductListingSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingSnippetContent::KEY_ROOT_SNIPPET_CODE => 'root',
            ProductListingSnippetContent::KEY_CONTAINER_SNIPPETS => [],
        ]);

        $metaSnippetContent = ProductListingSnippetContent::fromJson($json);
        $result = $metaSnippetContent->getSelectionCriteria();

        $className = SearchCriterion::class . $operation;
        $expectedCriterion = new $className($fieldName, $fieldValue);
        $expectedCriteria = CompositeSearchCriterion::createAnd($expectedCriterion);

        $this->assertEquals($expectedCriteria, $result);
    }

    public function testItReturnsThePageSnippetContainers()
    {
        $this->assertSame($this->containerSnippets, $this->pageMetaInfo->getContainerSnippets());
    }
}
