<?php

namespace Brera\DataPool\SearchEngine;

use Brera\Context\Context;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocument;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection;

abstract class AbstractSearchEngineTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchDocumentCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSearchDocumentCollection;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @var SearchEngine
     */
    private $searchEngine;

    protected function setUp()
    {
        $this->searchEngine = $this->createSearchEngineInstance();

        $this->stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);
        $this->stubContext = $this->createStubContext(['website' => 'ru']);
    }

    public function testSearchEngineInterfaceIsImplemented()
    {
        $this->assertInstanceOf(SearchEngine::class, $this->searchEngine);
    }

    public function testEmptyArrayIsReturnedRegardlessOfWhatHasBeenQueriedIfIndexIsEmpty()
    {
        $result = $this->searchEngine->query('bar', $this->stubContext);
        $this->assertCount(0, $result);
    }

    public function testEntryIsAddedIntoIndexAndThenFound()
    {
        $searchDocumentFieldValue = 'bar';
        $searchDocumentContent = 'qux';
        $searchDocumentFieldName = 'foo';
        
        $searchDocument = $this->createSearchDocument(
            [$searchDocumentFieldName => $searchDocumentFieldValue],
            $searchDocumentContent
        );

        $this->searchEngine->addSearchDocument($searchDocument);
        $result = $this->searchEngine->query($searchDocumentFieldValue, $this->stubContext);

        $this->assertEquals([$searchDocumentContent], $result);
    }

    public function testEmptyArrayIsReturnedIfQueryStringIsNotFoundInIndex()
    {
        $searchDocumentFields = ['foo' => 'bar'];
        $searchDocumentContent = null;
        $searchDocument = $this->createSearchDocument($searchDocumentFields, $searchDocumentContent);
        $this->searchEngine->addSearchDocument($searchDocument);
        $result = $this->searchEngine->query('baz', $this->stubContext);

        $this->assertCount(0, $result);
    }

    public function testMultipleEntriesAreAddedToIndex()
    {
        $keyword = 'bar';

        $searchDocumentAContent = 'contentA';
        $searchDocumentA = $this->createSearchDocument(['foo' => $keyword], $searchDocumentAContent);

        $searchDocumentBContent = 'contentB';
        $searchDocumentB = $this->createSearchDocument(['baz' => $keyword], $searchDocumentBContent);

        $this->stubSearchDocumentCollection->method('getDocuments')->willReturn([$searchDocumentA, $searchDocumentB]);
        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);
        $result = $this->searchEngine->query($keyword, $this->stubContext);

        $this->assertEmpty(array_diff([$searchDocumentAContent, $searchDocumentBContent], $result));
    }

    public function testOnlyEntriesContainingRequestedStringAreReturned()
    {
        $searchDocumentContent = 'content';
        $keyword = 'bar';

        $searchDocumentA = $this->createSearchDocument(['foo' => $keyword], $searchDocumentContent);
        $searchDocumentB = $this->createSearchDocument(['baz' => 'qux'], null);

        $this->stubSearchDocumentCollection->method('getDocuments')->willReturn([$searchDocumentA, $searchDocumentB]);
        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);
        $result = $this->searchEngine->query($keyword, $this->stubContext);

        $this->assertEquals([$searchDocumentContent], $result);
    }

    public function testOnlyMatchesWithMatchingContextsAreReturned()
    {
        $keyword = 'bar';

        $searchDocumentAContent = 'contentA';
        $stubSearchDocumentAContext = $this->createStubContext(['website' => 'value-1']);
        $searchDocumentA = $this->createSearchDocumentWithContext(
            ['foo' => $keyword],
            $searchDocumentAContent,
            $stubSearchDocumentAContext
        );

        $searchDocumentBContent = 'contentB';
        $stubSearchDocumentBContext = $this->createStubContext(['website' => 'value-2']);
        $searchDocumentB = $this->createSearchDocumentWithContext(
            ['foo' => $keyword],
            $searchDocumentBContent,
            $stubSearchDocumentBContext
        );

        $this->stubSearchDocumentCollection->method('getDocuments')->willReturn([$searchDocumentA, $searchDocumentB]);
        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);

        $stubQueryContext = $this->createStubContext(['website' => 'value-2']);
        $result = $this->searchEngine->query($keyword, $stubQueryContext);

        $this->assertEquals([$searchDocumentBContent], $result);
    }

    public function testPartialContextsAreMatched()
    {
        $searchDocumentAContent = 'contentA';
        $stubDocumentAContext = $this->createStubContext(['website' => 'value1', 'locale' => 'value2']);
        $searchDocumentA = $this->createSearchDocumentWithContext(
            ['foo' => 'bar'],
            $searchDocumentAContent,
            $stubDocumentAContext
        );

        $searchDocumentBContent = 'contentB';
        $stubDocumentBContext = $this->createStubContext(['website' => 'value1', 'locale' => 'value2']);
        $searchDocumentB = $this->createSearchDocumentWithContext(
            ['foo' => 'bar'],
            $searchDocumentBContent,
            $stubDocumentBContext
        );

        $this->stubSearchDocumentCollection->method('getDocuments')->willReturn([$searchDocumentA, $searchDocumentB]);
        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);

        $stubQueryContext = $this->createStubContext(['locale' => 'value2']);
        $result = $this->searchEngine->query('bar', $stubQueryContext);

        $this->assertArraysHasEqualElements([$searchDocumentAContent, $searchDocumentBContent], $result);
    }

    public function testContextPartsThatAreNotInSearchDocumentContextAreIgnored()
    {
        $stubDocumentContext = $this->createStubContext(['locale' => 'value2']);
        $searchDocumentContent = 'content';
        $searchDocument = $this->createSearchDocumentWithContext(
            ['foo' => 'bar'],
            $searchDocumentContent,
            $stubDocumentContext
        );

        $this->stubSearchDocumentCollection->method('getDocuments')->willReturn([$searchDocument]);
        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);

        $stubQueryContext = $this->createStubContext(['website' => 'value1', 'locale' => 'value2']);
        $result = $this->searchEngine->query('bar', $stubQueryContext);

        $this->assertEquals([$searchDocumentContent], $result);
    }

    public function testEntriesContainingRequestedStringAreReturned()
    {
        $searchDocumentAContent = 'contentA';
        $searchDocumentA = $this->createSearchDocument(['foo' => 'barbarism'], $searchDocumentAContent);

        $searchDocumentBContent = 'contentB';
        $searchDocumentB = $this->createSearchDocument(['baz' => 'cabaret'], $searchDocumentBContent);

        $this->stubSearchDocumentCollection->method('getDocuments')->willReturn([$searchDocumentA, $searchDocumentB]);
        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);
        $result = $this->searchEngine->query('bar', $this->stubContext);

        $this->assertEmpty(array_diff([$searchDocumentAContent, $searchDocumentBContent], $result));
    }

    public function testUniqueEntriesAreReturned()
    {
        $searchDocumentContent = 'content';
        $searchDocumentA = $this->createSearchDocument(['foo' => 'barbarism'], $searchDocumentContent);
        $searchDocumentB = $this->createSearchDocument(['baz' => 'cabaret'], $searchDocumentContent);

        $this->stubSearchDocumentCollection->method('getDocuments')->willReturn([$searchDocumentA, $searchDocumentB]);
        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);
        $result = $this->searchEngine->query('bar', $this->stubContext);

        $this->assertEquals([$searchDocumentContent], $result);
    }

    public function testEmptyArrayIsReturnedForEmptySearchCriteria()
    {
        $mockCriteria = $this->createMockCriteria(SearchCriteria::OR_CONDITION, []);

        $result = $this->searchEngine->getContentOfSearchDocumentsMatchingCriteria($mockCriteria, $this->stubContext);

        $this->assertSame([], $result);
    }

    public function testEmptyArrayIsReturnedIfNoMatchesAreFound()
    {
        $criterion = SearchCriterion::create('test-field', 'test-search-term', '=');
        $mockCriteria = $this->createMockCriteria(SearchCriteria::OR_CONDITION, [$criterion]);

        $result = $this->searchEngine->getContentOfSearchDocumentsMatchingCriteria($mockCriteria, $this->stubContext);

        $this->assertSame([], $result);
    }

    public function testArrayWithOneProductIdMatchingCriteriaIsReturned()
    {
        $searchDocumentContent = 'content';
        $dummyFieldName = 'test-field-name';
        $dummyQueryTerm = 'test-query-term';

        $searchDocument = $this->createSearchDocument([$dummyFieldName => $dummyQueryTerm], $searchDocumentContent);
        $this->searchEngine->addSearchDocument($searchDocument);

        $criterion = SearchCriterion::create($dummyFieldName, $dummyQueryTerm, '=');
        $mockCriteria = $this->createMockCriteria(SearchCriteria::OR_CONDITION, [$criterion]);
        $result = $this->searchEngine->getContentOfSearchDocumentsMatchingCriteria($mockCriteria, $this->stubContext);

        $this->assertEquals([$searchDocumentContent], $result);
    }

    public function testArrayWithTwoProductIdsMatchingAnyCriteriaIsReturned()
    {
        $dummyProductId1 = 'id01';
        $dummyProductId2 = 'id02';
        $dummyFieldName1 = 'foo';
        $dummyFieldValue1 = 'bar';
        $dummyFieldName2 = 'baz';
        $dummyFieldValue2 = 'qux';

        $searchDocumentA = $this->createSearchDocument([$dummyFieldName1 => $dummyFieldValue1], $dummyProductId1);
        $searchDocumentB = $this->createSearchDocument([$dummyFieldName2 => $dummyFieldValue2], $dummyProductId2);

        $this->stubSearchDocumentCollection->method('getDocuments')->willReturn([$searchDocumentA, $searchDocumentB]);
        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);

        $criterion1 = SearchCriterion::create($dummyFieldName1, $dummyFieldValue1, '=');
        $criterion2 = SearchCriterion::create($dummyFieldName2, $dummyFieldValue2, '=');
        $mockCriteria = $this->createMockCriteria(SearchCriteria::OR_CONDITION, [$criterion1, $criterion2]);

        $result = $this->searchEngine->getContentOfSearchDocumentsMatchingCriteria($mockCriteria, $this->stubContext);

        $this->assertContains($dummyProductId1, $result);
        $this->assertContains($dummyProductId2, $result);
    }

    public function testArrayWithTwoProductIdsMatchingAllCriteriaAreReturned()
    {
        $dummyProductId1 = 'id01';
        $dummyProductId2 = 'id02';
        $dummyProductId3 = 'id03';
        $dummyFieldName1 = 'foo';
        $dummyFieldValue1 = 'bar';
        $dummyFieldName2 = 'baz';
        $dummyFieldValue2 = 'qux';

        $searchDocumentA = $this->createSearchDocument([
            $dummyFieldName1 => $dummyFieldValue1,
            $dummyFieldName2 => $dummyFieldValue2
        ], $dummyProductId1);

        $searchDocumentB = $this->createSearchDocument([
            $dummyFieldName1 => $dummyFieldValue1,
            $dummyFieldName2 => $dummyFieldValue2
        ], $dummyProductId2);

        $searchDocumentC = $this->createSearchDocument([$dummyFieldName1 => $dummyFieldValue1], $dummyProductId2);

        $this->stubSearchDocumentCollection->method('getDocuments')
            ->willReturn([$searchDocumentA, $searchDocumentB, $searchDocumentC]);
        $this->searchEngine->addSearchDocumentCollection($this->stubSearchDocumentCollection);

        $criterion1 = SearchCriterion::create($dummyFieldName1, $dummyFieldValue1, '=');
        $criterion2 = SearchCriterion::create($dummyFieldName2, $dummyFieldValue2, '=');
        $mockCriteria = $this->createMockCriteria(SearchCriteria::AND_CONDITION, [$criterion1, $criterion2]);

        $result = $this->searchEngine->getContentOfSearchDocumentsMatchingCriteria($mockCriteria, $this->stubContext);

        $this->assertContains($dummyProductId1, $result);
        $this->assertContains($dummyProductId2, $result);
        $this->assertNotContains($dummyProductId3, $result);
    }

    /**
     * @return SearchEngine
     */
    abstract protected function createSearchEngineInstance();

    /**
     * @param string[] $fields
     * @param mixed $content
     * @return SearchDocument
     */
    private function createSearchDocument(array $fields, $content)
    {
        return $this->createSearchDocumentWithContext($fields, $content, $this->stubContext);
    }

    /**
     * @param string[] $fields
     * @param mixed $content
     * @param Context $context
     * @return SearchDocument
     */
    private function createSearchDocumentWithContext(array $fields, $content, Context $context)
    {
        return new SearchDocument(SearchDocumentFieldCollection::fromArray($fields), $context, $content);
    }

    /**
     * @param mixed[] $array1
     * @param mixed[] $array2
     */
    private function assertArraysHasEqualElements(array $array1, array $array2)
    {
        $this->assertEmpty(array_diff($array1, $array2));
    }

    /**
     * @param string $condition
     * @param \PHPUnit_Framework_MockObject_MockObject[] $mockCriteriaToReturn
     * @return SearchCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createMockCriteria($condition, array $mockCriteriaToReturn)
    {
        $mockCriteria = $this->getMock(SearchCriteria::class, [], [], '', false);
        $mockCriteria->method('hasAndCondition')->willReturn(SearchCriteria::AND_CONDITION === $condition);
        $mockCriteria->method('hasOrCondition')->willReturn(SearchCriteria::OR_CONDITION === $condition);
        $mockCriteria->method('getCriteria')->willReturn($mockCriteriaToReturn);

        return $mockCriteria;
    }

    /**
     * @param string[] $contextDataSet
     * @return Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubContext(array $contextDataSet)
    {
        $contextDataSet['version'] = '-1';

        $contextValueMap = [];
        foreach ($contextDataSet as $key => $value) {
            $contextValueMap[] = [$key, $value];
        }

        $stubContext = $this->getMock(Context::class);
        $stubContext->method('getValue')->willReturnMap($contextValueMap);
        $stubContext->method('supportsCode')->willReturnCallback(function ($contextPartCode) use ($contextDataSet) {
            return isset($contextDataSet[$contextPartCode]);
        });
        $stubContext->method('getSupportedCodes')->willReturn(array_keys($contextDataSet));

        return $stubContext;
    }
}
