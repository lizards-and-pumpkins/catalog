<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion;
use LizardsAndPumpkins\Product\Exception\MalformedSearchCriteriaMetaException;

/**
 * @covers \LizardsAndPumpkins\Product\ProductListingMetaInfoSnippetContent
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 */
class ProductListingMetaInfoSnippetContentTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ProductListingMetaInfoSnippetContent
     */
    private $pageMetaInfo;

    /**
     * @var string
     */
    private $rootSnippetCode = 'root-snippet-code';

    /**
     * @var CompositeSearchCriterion|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSelectionCriteria;

    protected function setUp()
    {
        $this->stubSelectionCriteria = $this->getMock(CompositeSearchCriterion::class, [], [], '', false);
        $this->stubSelectionCriteria->method('jsonSerialize')
            ->willReturn(['condition' => CompositeSearchCriterion::AND_CONDITION, 'criteria' => []]);

        $pageSnippetCodes = [$this->rootSnippetCode];

        $this->pageMetaInfo = ProductListingMetaInfoSnippetContent::create(
            $this->stubSelectionCriteria,
            $this->rootSnippetCode,
            $pageSnippetCodes
        );
    }

    public function testArrayIsReturned()
    {
        $this->assertInternalType('array', $this->pageMetaInfo->getInfo());
    }

    public function testExpectedArrayKeysArePresentInJsonContent()
    {
        $keys = [
            ProductListingMetaInfoSnippetContent::KEY_CRITERIA,
            ProductListingMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE,
            ProductListingMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES
        ];

        $result = $this->pageMetaInfo->getInfo();

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $result, sprintf('Page meta info array is lacking "%s" key', $key));
        }
    }

    public function testExceptionIsThrownIfTheRootSnippetCodeIsNoString()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        ProductListingMetaInfoSnippetContent::create($this->stubSelectionCriteria, 1.0, []);
    }

    public function testRootSnippetCodeIsAddedToTheSnippetCodeListIfNotPresent()
    {
        $rootSnippetCode = 'root-snippet-code';
        $pageMetaInfo = ProductListingMetaInfoSnippetContent::create(
            $this->stubSelectionCriteria,
            $rootSnippetCode,
            []
        );
        $this->assertContains(
            $rootSnippetCode,
            $pageMetaInfo->getInfo()[ProductListingMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES]
        );
    }

    public function testJsonConstructorIsPresent()
    {
        $pageMetaInfo = ProductListingMetaInfoSnippetContent::fromJson(json_encode($this->pageMetaInfo->getInfo()));
        $this->assertInstanceOf(ProductListingMetaInfoSnippetContent::class, $pageMetaInfo);
    }
    
    public function testExceptionIsThrownInCaseOfJsonErrors()
    {
        $this->setExpectedException(\OutOfBoundsException::class);
        ProductListingMetaInfoSnippetContent::fromJson('malformed-json');
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
        ProductListingMetaInfoSnippetContent::fromJson(json_encode($pageInfo));
    }

    /**
     * @return array[]
     */
    public function pageInfoArrayKeyProvider()
    {
        return [
            [ProductListingMetaInfoSnippetContent::KEY_CRITERIA],
            [ProductDetailPageMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE],
            [ProductDetailPageMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES],
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
            ProductListingMetaInfoSnippetContent::KEY_CRITERIA => [],
            ProductListingMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE => ''
        ]);

        ProductListingMetaInfoSnippetContent::fromJson($json);
    }

    public function testExceptionIsThrownIfSearchCriteriaCriteriaIsMissing()
    {
        $this->setExpectedException(MalformedSearchCriteriaMetaException::class, 'Missing criteria.');

        $json = json_encode([
            ProductListingMetaInfoSnippetContent::KEY_CRITERIA => ['condition' => ''],
            ProductListingMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE => ''
        ]);

        ProductListingMetaInfoSnippetContent::fromJson($json);
    }

    public function testExceptionIsThrownIfCriterionFieldNameIsMissing()
    {
        $this->setExpectedException(MalformedSearchCriteriaMetaException::class, 'Missing criterion field name.');

        $json = json_encode([
            ProductListingMetaInfoSnippetContent::KEY_CRITERIA => [
                'condition' => '',
                'criteria'  => [[]]
            ],
            ProductListingMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE => ''
        ]);

        ProductListingMetaInfoSnippetContent::fromJson($json);
    }

    public function testExceptionIsThrownIfCriterionFieldValueIsMissing()
    {
        $this->setExpectedException(MalformedSearchCriteriaMetaException::class, 'Missing criterion field value.');

        $json = json_encode([
            ProductListingMetaInfoSnippetContent::KEY_CRITERIA => [
                'condition' => '',
                'criteria'  => [
                    ['fieldName' => 'foo']
                ]
            ],
            ProductListingMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE => ''
        ]);

        ProductListingMetaInfoSnippetContent::fromJson($json);
    }

    public function testExceptionIsThrownIfCriterionOperationIsMissing()
    {
        $this->setExpectedException(MalformedSearchCriteriaMetaException::class, 'Missing criterion operation.');

        $json = json_encode([
            ProductListingMetaInfoSnippetContent::KEY_CRITERIA => [
                'condition' => '',
                'criteria'  => [
                    ['fieldName' => 'foo', 'fieldValue' => 'bar']
                ]
            ],
            ProductListingMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE => ''
        ]);

        ProductListingMetaInfoSnippetContent::fromJson($json);
    }

    public function testExceptionIsThrownIfCriterionOperationIsInvalid()
    {
        $invalidOperationName = 'baz';

        $this->setExpectedException(
            MalformedSearchCriteriaMetaException::class,
            sprintf('Unknown criterion operation "%s"', $invalidOperationName)
        );

        $json = json_encode([
            ProductListingMetaInfoSnippetContent::KEY_CRITERIA => [
                'condition' => '',
                'criteria'  => [
                    ['fieldName' => 'foo', 'fieldValue' => 'bar', 'operation' => $invalidOperationName]
                ]
            ],
            ProductListingMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE => ''
        ]);

        ProductListingMetaInfoSnippetContent::fromJson($json);
    }

    public function testProductListingMetaInfoIsCreatedWithPassedSearchCriteria()
    {
        $fieldName = 'foo';
        $fieldValue = 'bar';
        $operation = 'Equal';

        $json = json_encode([
            ProductListingMetaInfoSnippetContent::KEY_CRITERIA => [
                'condition' => '',
                'criteria'  => [
                    ['fieldName' => $fieldName, 'fieldValue' => $fieldValue, 'operation' => $operation]
                ]
            ],
            ProductListingMetaInfoSnippetContent::KEY_PAGE_SNIPPET_CODES => [],
            ProductListingMetaInfoSnippetContent::KEY_ROOT_SNIPPET_CODE => ''
        ]);

        $metaSnippetContent = ProductListingMetaInfoSnippetContent::fromJson($json);
        $result = $metaSnippetContent->getSelectionCriteria();

        $className = SearchCriterion::class . $operation;
        $expectedCriterion = call_user_func([$className, 'create'], $fieldName, $fieldValue);
        $expectedCriteria = CompositeSearchCriterion::createAnd($expectedCriterion);

        $this->assertEquals($expectedCriteria, $result);
    }
}
