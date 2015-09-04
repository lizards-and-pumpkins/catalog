<?php

namespace Brera\Product;

use Brera\Context\Context;
use Brera\DataPool\DataPoolReader;
use Brera\DataPool\SearchEngine\SearchCriteria;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocument;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentField;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection;

/**
 * @covers \Brera\Product\FilterNavigationFilterCollection
 * @uses   \Brera\DataPool\SearchEngine\CompositeSearchCriterion
 * @uses   \Brera\DataPool\SearchEngine\SearchCriterion
 * @uses   \Brera\Product\FilterNavigationFilter
 * @uses   \Brera\Product\FilterNavigationFilterValue
 * @uses   \Brera\Product\FilterNavigationFilterValueCollection
 */
class FilterNavigationFilterCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DataPoolReader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubDataPoolReader;

    /**
     * @var FilterNavigationFilterCollection
     */
    private $filterCollection;

    /**
     * @var Context|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubContext;

    /**
     * @var SearchCriteria|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSearchCriteria;

    /**
     * @param string $key
     * @param string $value
     * @return SearchDocumentField|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSearchDocumentField($key, $value)
    {
        $stubSearchDocumentField = $this->getMock(SearchDocumentField::class, [], [], '', false);
        $stubSearchDocumentField->method('getKey')->willReturn($key);
        $stubSearchDocumentField->method('getValue')->willReturn($value);

        return $stubSearchDocumentField;
    }

    /**
     * @param SearchDocumentField[] $fields
     * @return SearchDocument|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSearchDocumentWithGivenFields(array $fields)
    {
        $stubSearchDocumentFieldsCollection = $this->getMock(SearchDocumentFieldCollection::class, [], [], '', false);
        $stubSearchDocumentFieldsCollection->method('getFields')->willReturn($fields);

        $stubSearchDocument = $this->getMock(SearchDocument::class, [], [], '', false);
        $stubSearchDocument->method('getFieldsCollection')->willReturn($stubSearchDocumentFieldsCollection);

        return $stubSearchDocument;
    }

    protected function setUp()
    {
        $this->stubDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);
        $this->filterCollection = new FilterNavigationFilterCollection($this->stubDataPoolReader);

        $this->stubContext = $this->getMock(Context::class);
        $this->stubSearchCriteria = $this->getMock(SearchCriteria::class, [], [], '', false);
    }

    public function testCountableInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\Countable::class, $this->filterCollection);
    }

    public function testExceptionIsThrownDuringAttemptToRetrieveFiltersWithoutInitializingCollection()
    {
        $this->setExpectedException(FilterCollectionInNotInitializedException::class);
        $this->filterCollection->getFilters();
    }

    public function testCollectionOnlyIncludesFiltersConfiguredForFilterNavigation()
    {
        $selectedFilters = ['foo' => []];

        $stubField1 = $this->createStubSearchDocumentField('foo', 'baz');
        $stubField2 = $this->createStubSearchDocumentField('bar', 'qux');

        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields([$stubField1, $stubField2]);

        /** @var SearchDocumentCollection|\PHPUnit_Framework_MockObject_MockObject $stubSearchDocumentCollection */
        $stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);
        $stubSearchDocumentCollection->method('getDocuments')->willReturn([$stubSearchDocument]);

        $this->filterCollection->initialize(
            $stubSearchDocumentCollection,
            $this->stubSearchCriteria,
            $selectedFilters,
            $this->stubContext
        );
        $result = $this->filterCollection->getFilters();

        $this->assertCount(1, $result);

        $this->assertSame('foo', $result[0]->getCode());
        $this->assertCount(1, $result[0]->getValuesCollection());
        $this->assertSame('baz', $result[0]->getValuesCollection()->getFilterValues()[0]->getValue());
        $this->assertSame(1, $result[0]->getValuesCollection()->getFilterValues()[0]->getCount());
        $this->assertFalse($result[0]->getValuesCollection()->getFilterValues()[0]->isSelected());
    }

    public function testCollectionReflectsValuesFromSearchDocumentFieldsIfNoFiltersAreSelected()
    {
        $selectedFilters = ['foo' => [], 'bar' => []];

        $stubField = $this->createStubSearchDocumentField('foo', 'qux');

        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields([$stubField]);

        /** @var SearchDocumentCollection|\PHPUnit_Framework_MockObject_MockObject $stubSearchDocumentCollection */
        $stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);
        $stubSearchDocumentCollection->method('getDocuments')->willReturn([$stubSearchDocument]);

        $this->filterCollection->initialize(
            $stubSearchDocumentCollection,
            $this->stubSearchCriteria,
            $selectedFilters,
            $this->stubContext
        );
        $result = $this->filterCollection->getFilters();

        $this->assertCount(1, $result);

        $this->assertSame('foo', $result[0]->getCode());
        $this->assertCount(1, $result[0]->getValuesCollection());
        $this->assertSame('qux', $result[0]->getValuesCollection()->getFilterValues()[0]->getValue());
        $this->assertSame(1, $result[0]->getValuesCollection()->getFilterValues()[0]->getCount());
        $this->assertFalse($result[0]->getValuesCollection()->getFilterValues()[0]->isSelected());
    }

    public function testOnlyFiltersWhichHaveMatchingValuesInProductsCollectionAreReturned()
    {
        $selectedFilters = ['foo' => ['baz'], 'bar' => []];

        $stubField = $this->createStubSearchDocumentField('foo', 'baz');
        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields([$stubField]);

        /** @var SearchDocumentCollection|\PHPUnit_Framework_MockObject_MockObject $stubFilteredDocumentCollection */
        $stubFilteredDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);
        $stubFilteredDocumentCollection->method('getDocuments')->willReturn([$stubSearchDocument]);

        /** @var SearchDocumentCollection|\PHPUnit_Framework_MockObject_MockObject $stubUnfilteredDocumentCollection */
        $stubUnfilteredDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);
        $stubUnfilteredDocumentCollection->method('getDocuments')->willReturn([$stubSearchDocument]);

        $this->stubDataPoolReader->method('getSearchDocumentsMatchingCriteria')
            ->willReturn($stubUnfilteredDocumentCollection);

        $this->filterCollection->initialize(
            $stubFilteredDocumentCollection,
            $this->stubSearchCriteria,
            $selectedFilters,
            $this->stubContext
        );
        $result = $this->filterCollection->getFilters();

        $this->assertCount(1, $result);

        $this->assertSame('foo', $result[0]->getCode());
        $this->assertCount(1, $result[0]->getValuesCollection());
        $this->assertSame('baz', $result[0]->getValuesCollection()->getFilterValues()[0]->getValue());
        $this->assertSame(1, $result[0]->getValuesCollection()->getFilterValues()[0]->getCount());
        $this->assertTrue($result[0]->getValuesCollection()->getFilterValues()[0]->isSelected());
    }

    public function testSelectedFiltersHaveSiblingValuesForBroadeningProductsCollection()
    {
        $selectedFilters = ['foo' => ['baz'], 'bar' => ['0 Eur - 100 Eur']];

        $stubField1 = $this->createStubSearchDocumentField('foo', 'baz');
        $stubField2 = $this->createStubSearchDocumentField('foo', 'qux');
        $stubField3 = $this->createStubSearchDocumentField('bar', '0 Eur - 100 Eur');
        $stubField4 = $this->createStubSearchDocumentField('bar', '100 Eur - 200 Eur');

        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields(
            [$stubField1, $stubField2, $stubField3, $stubField4]
        );

        /** @var SearchDocumentCollection|\PHPUnit_Framework_MockObject_MockObject $stubFilteredDocumentCollection */
        $stubFilteredDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);
        $stubFilteredDocumentCollection->method('getDocuments')->willReturn([$stubSearchDocument]);

        /** @var SearchDocumentCollection|\PHPUnit_Framework_MockObject_MockObject $stubUnfilteredDocumentCollection */
        $stubUnfilteredDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);
        $stubUnfilteredDocumentCollection->method('getDocuments')->willReturn([$stubSearchDocument]);

        $this->stubDataPoolReader->method('getSearchDocumentsMatchingCriteria')
            ->willReturn($stubUnfilteredDocumentCollection);

        $this->filterCollection->initialize(
            $stubFilteredDocumentCollection,
            $this->stubSearchCriteria,
            $selectedFilters,
            $this->stubContext
        );
        $result = $this->filterCollection->getFilters();

        $this->assertCount(2, $result);

        $this->assertSame('foo', $result[0]->getCode());
        $this->assertCount(2, $result[0]->getValuesCollection());
        $this->assertSame('baz', $result[0]->getValuesCollection()->getFilterValues()[0]->getValue());
        $this->assertSame(1, $result[0]->getValuesCollection()->getFilterValues()[0]->getCount());
        $this->assertTrue($result[0]->getValuesCollection()->getFilterValues()[0]->isSelected());
        $this->assertSame('qux', $result[0]->getValuesCollection()->getFilterValues()[1]->getValue());
        $this->assertSame(1, $result[0]->getValuesCollection()->getFilterValues()[1]->getCount());
        $this->assertFalse($result[0]->getValuesCollection()->getFilterValues()[1]->isSelected());

        $this->assertSame('bar', $result[1]->getCode());
        $this->assertCount(2, $result[1]->getValuesCollection());
        $this->assertSame('0 Eur - 100 Eur', $result[1]->getValuesCollection()->getFilterValues()[0]->getValue());
        $this->assertSame(1, $result[1]->getValuesCollection()->getFilterValues()[0]->getCount());
        $this->assertTrue($result[1]->getValuesCollection()->getFilterValues()[0]->isSelected());
        $this->assertSame('100 Eur - 200 Eur', $result[1]->getValuesCollection()->getFilterValues()[1]->getValue());
        $this->assertSame(1, $result[1]->getValuesCollection()->getFilterValues()[1]->getCount());
        $this->assertFalse($result[1]->getValuesCollection()->getFilterValues()[1]->isSelected());
    }

    public function testSelectedFiltersAndValuesAreReturned()
    {
        $selectedFilters = ['foo' => ['baz'], 'bar' => []];

        $stubField = $this->createStubSearchDocumentField('foo', 'baz');
        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields([$stubField]);

        /** @var SearchDocumentCollection|\PHPUnit_Framework_MockObject_MockObject $stubFilteredDocumentCollection */
        $stubFilteredDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);
        $stubFilteredDocumentCollection->method('getDocuments')->willReturn([$stubSearchDocument]);

        $this->stubDataPoolReader->method('getSearchDocumentsMatchingCriteria')
            ->willReturn($stubFilteredDocumentCollection);

        $this->filterCollection->initialize(
            $stubFilteredDocumentCollection,
            $this->stubSearchCriteria,
            $selectedFilters,
            $this->stubContext
        );

        $result = $this->filterCollection->getSelectedFilters();

        $this->assertSame($selectedFilters, $result);
    }
}
