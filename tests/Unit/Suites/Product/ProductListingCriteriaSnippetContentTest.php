<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion;
use LizardsAndPumpkins\Product\Exception\MalformedSearchCriteriaMetaException;

/**
 * @covers \LizardsAndPumpkins\Product\ProductListingCriteriaSnippetContent
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 * @uses   \LizardsAndPumpkins\SnippetContainer
 */
class ProductListingCriteriaSnippetContentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListingCriteriaSnippetContent
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
        $this->stubSelectionCriteria = $this->getMock(CompositeSearchCriterion::class, [], [], '', false);
        $this->stubSelectionCriteria->method('jsonSerialize')->willReturn([
            'condition' => CompositeSearchCriterion::AND_CONDITION,
            'criteria' => [[
                'condition' => CompositeSearchCriterion::AND_CONDITION,
                'criteria' => []
            ]]
        ]);

        $pageSnippetCodes = [$this->rootSnippetCode];
        
        $this->pageMetaInfo = ProductListingCriteriaSnippetContent::create(
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
            ProductListingCriteriaSnippetContent::KEY_CRITERIA,
            ProductListingCriteriaSnippetContent::KEY_ROOT_SNIPPET_CODE,
            ProductListingCriteriaSnippetContent::KEY_PAGE_SNIPPET_CODES,
            ProductListingCriteriaSnippetContent::KEY_CONTAINER_SNIPPETS,
        ];

        $result = $this->pageMetaInfo->getInfo();

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $result, sprintf('Page meta info array is lacking "%s" key', $key));
        }
    }

    public function testExceptionIsThrownIfTheRootSnippetCodeIsNoString()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        ProductListingCriteriaSnippetContent::create($this->stubSelectionCriteria, 1.0, [], []);
    }

    public function testRootSnippetCodeIsAddedToTheSnippetCodeListIfNotPresent()
    {
        $rootSnippetCode = 'root-snippet-code';
        $pageMetaInfo = ProductListingCriteriaSnippetContent::create(
            $this->stubSelectionCriteria,
            $rootSnippetCode,
            [],
            []
        );
        $this->assertContains(
            $rootSnippetCode,
            $pageMetaInfo->getInfo()[ProductListingCriteriaSnippetContent::KEY_PAGE_SNIPPET_CODES]
        );
    }

    public function testJsonConstructorIsPresent()
    {
        $pageMetaInfo = ProductListingCriteriaSnippetContent::fromJson(json_encode($this->pageMetaInfo->getInfo()));
        $this->assertInstanceOf(ProductListingCriteriaSnippetContent::class, $pageMetaInfo);
    }
    
    public function testExceptionIsThrownInCaseOfJsonErrors()
    {
        $this->setExpectedException(\OutOfBoundsException::class);
        ProductListingCriteriaSnippetContent::fromJson('malformed-json');
    }

    /**
     * @dataProvider pageInfoArrayKeyProvider
     * @param string $key
     */
    public function testExceptionIsThrownIfARequiredKeyIsMissing($key)
    {
        $this->setExpectedException(\RuntimeException::class, 'Missing key in input JSON');
        $pageInfo = $this->pageMetaInfo->getInfo();
        unset($pageInfo[$key]);
        ProductListingCriteriaSnippetContent::fromJson(json_encode($pageInfo));
    }

    /**
     * @return array[]
     */
    public function pageInfoArrayKeyProvider()
    {
        return [
            [ProductListingCriteriaSnippetContent::KEY_CRITERIA],
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
        $this->setExpectedException(MalformedSearchCriteriaMetaException::class, 'Missing criteria condition.');

        $json = json_encode([
            ProductListingCriteriaSnippetContent::KEY_CRITERIA => [],
            ProductListingCriteriaSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingCriteriaSnippetContent::KEY_ROOT_SNIPPET_CODE => '',
            ProductListingCriteriaSnippetContent::KEY_CONTAINER_SNIPPETS => [],
        ]);

        ProductListingCriteriaSnippetContent::fromJson($json);
    }

    public function testExceptionIsThrownIfSearchCriteriaCriteriaIsMissing()
    {
        $this->setExpectedException(MalformedSearchCriteriaMetaException::class, 'Missing criteria.');

        $json = json_encode([
            ProductListingCriteriaSnippetContent::KEY_CRITERIA => [
                'condition' => CompositeSearchCriterion::AND_CONDITION
            ],
            ProductListingCriteriaSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingCriteriaSnippetContent::KEY_ROOT_SNIPPET_CODE => '',
            ProductListingCriteriaSnippetContent::KEY_CONTAINER_SNIPPETS => [],
        ]);

        ProductListingCriteriaSnippetContent::fromJson($json);
    }

    public function testExceptionIsThrownIfCriterionFieldNameIsMissing()
    {
        $this->setExpectedException(MalformedSearchCriteriaMetaException::class, 'Missing criterion field name.');

        $json = json_encode([
            ProductListingCriteriaSnippetContent::KEY_CRITERIA => [
                'condition' => CompositeSearchCriterion::AND_CONDITION,
                'criteria'  => [[]]
            ],
            ProductListingCriteriaSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingCriteriaSnippetContent::KEY_ROOT_SNIPPET_CODE => '',
            ProductListingCriteriaSnippetContent::KEY_CONTAINER_SNIPPETS => [],
        ]);

        ProductListingCriteriaSnippetContent::fromJson($json);
    }

    public function testExceptionIsThrownIfCriterionFieldValueIsMissing()
    {
        $this->setExpectedException(MalformedSearchCriteriaMetaException::class, 'Missing criterion field value.');

        $json = json_encode([
            ProductListingCriteriaSnippetContent::KEY_CRITERIA => [
                'condition' => CompositeSearchCriterion::AND_CONDITION,
                'criteria'  => [
                    ['fieldName' => 'foo']
                ]
            ],
            ProductListingCriteriaSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingCriteriaSnippetContent::KEY_ROOT_SNIPPET_CODE => '',
            ProductListingCriteriaSnippetContent::KEY_CONTAINER_SNIPPETS => [],
        ]);

        ProductListingCriteriaSnippetContent::fromJson($json);
    }

    public function testExceptionIsThrownIfCriterionOperationIsMissing()
    {
        $this->setExpectedException(MalformedSearchCriteriaMetaException::class, 'Missing criterion operation.');

        $json = json_encode([
            ProductListingCriteriaSnippetContent::KEY_CRITERIA => [
                'condition' => CompositeSearchCriterion::AND_CONDITION,
                'criteria'  => [
                    ['fieldName' => 'foo', 'fieldValue' => 'bar']
                ]
            ],
            ProductListingCriteriaSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingCriteriaSnippetContent::KEY_ROOT_SNIPPET_CODE => '',
            ProductListingCriteriaSnippetContent::KEY_CONTAINER_SNIPPETS => [],
        ]);

        ProductListingCriteriaSnippetContent::fromJson($json);
    }

    public function testExceptionIsThrownIfCriterionOperationIsInvalid()
    {
        $invalidOperationName = 'baz';

        $this->setExpectedException(
            MalformedSearchCriteriaMetaException::class,
            sprintf('Unknown criterion operation "%s"', $invalidOperationName)
        );

        $json = json_encode([
            ProductListingCriteriaSnippetContent::KEY_CRITERIA => [
                'condition' => CompositeSearchCriterion::AND_CONDITION,
                'criteria'  => [
                    ['fieldName' => 'foo', 'fieldValue' => 'bar', 'operation' => $invalidOperationName]
                ]
            ],
            ProductListingCriteriaSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingCriteriaSnippetContent::KEY_ROOT_SNIPPET_CODE => '',
            ProductListingCriteriaSnippetContent::KEY_CONTAINER_SNIPPETS => [],
        ]);

        ProductListingCriteriaSnippetContent::fromJson($json);
    }

    public function testProductListingCriteriaIsCreatedWithPassedSearchCriteria()
    {
        $fieldName = 'foo';
        $fieldValue = 'bar';
        $operation = 'Equal';

        $json = json_encode([
            ProductListingCriteriaSnippetContent::KEY_CRITERIA => [
                'condition' => CompositeSearchCriterion::AND_CONDITION,
                'criteria'  => [
                    ['fieldName' => $fieldName, 'fieldValue' => $fieldValue, 'operation' => $operation]
                ]
            ],
            ProductListingCriteriaSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingCriteriaSnippetContent::KEY_ROOT_SNIPPET_CODE => '',
            ProductListingCriteriaSnippetContent::KEY_CONTAINER_SNIPPETS => [],
        ]);

        $metaSnippetContent = ProductListingCriteriaSnippetContent::fromJson($json);
        $result = $metaSnippetContent->getSelectionCriteria();

        $className = SearchCriterion::class . $operation;
        $expectedCriterion = call_user_func([$className, 'create'], $fieldName, $fieldValue);
        $expectedCriteria = CompositeSearchCriterion::createAnd($expectedCriterion);

        $this->assertEquals($expectedCriteria, $result);
    }

    public function testItReturnsThePageSnippetContainers()
    {
        $this->assertSame($this->containerSnippets, $this->pageMetaInfo->getContainerSnippets());
    }
}
