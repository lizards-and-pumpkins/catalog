<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteria;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriteriaBuilder;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocument;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection;
use LizardsAndPumpkins\Product\Exception\FilterCollectionInNotInitializedException;
use LizardsAndPumpkins\Renderer\Translation\Translator;
use LizardsAndPumpkins\Renderer\Translation\TranslatorRegistry;

/**
 * @covers \LizardsAndPumpkins\Product\FilterNavigationFilterCollection
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterion
 * @uses   \LizardsAndPumpkins\Product\FilterNavigationFilter
 * @uses   \LizardsAndPumpkins\Product\FilterNavigationFilterOption
 * @uses   \LizardsAndPumpkins\Product\FilterNavigationFilterOptionCollection
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
     * @var SearchCriteriaBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSearchCriteriaBuilder;

    /**
     * @param string $key
     * @param string[] $values
     * @return SearchDocumentField|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSearchDocumentField($key, array $values)
    {
        $stubSearchDocumentField = $this->getMock(SearchDocumentField::class, [], [], '', false);
        $stubSearchDocumentField->method('getKey')->willReturn($key);
        $stubSearchDocumentField->method('getValues')->willReturn($values);

        return $stubSearchDocumentField;
    }

    /**
     * @param SearchDocumentField[] $fields
     * @return SearchDocument|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSearchDocumentWithGivenFields(array $fields)
    {
        $stubSearchDocumentFieldsCollection = $this->getMock(SearchDocumentFieldCollection::class, [], [], '', false);
        $stubSearchDocumentFieldsCollection->method('getIterator')->willReturn(new \ArrayIterator($fields));

        $stubSearchDocument = $this->getMock(SearchDocument::class, [], [], '', false);
        $stubSearchDocument->method('getFieldsCollection')->willReturn($stubSearchDocumentFieldsCollection);

        return $stubSearchDocument;
    }

    /**
     * @param SearchDocument ...$searchDocuments
     * @return SearchDocumentCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private function createStubSearchDocumentCollection(SearchDocument ...$searchDocuments)
    {
        $stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);
        $stubSearchDocumentCollection->method('getIterator')->willReturn(new \ArrayIterator($searchDocuments));

        return $stubSearchDocumentCollection;
    }

    protected function setUp()
    {
        $this->stubDataPoolReader = $this->getMock(DataPoolReader::class, [], [], '', false);

        $stubTranslator = $this->getMock(Translator::class);

        /** @var TranslatorRegistry|\PHPUnit_Framework_MockObject_MockObject $stubTranslatorRegistry */
        $stubTranslatorRegistry = $this->getMock(TranslatorRegistry::class, [], [], '', false);
        $stubTranslatorRegistry->method('getTranslatorForLocale')->willReturn($stubTranslator);

        $this->stubSearchCriteriaBuilder = $this->getMock(SearchCriteriaBuilder::class);

        $this->filterCollection = new FilterNavigationFilterCollection(
            $this->stubDataPoolReader,
            $stubTranslatorRegistry,
            $this->stubSearchCriteriaBuilder
        );

        $this->stubContext = $this->getMock(Context::class);
        $this->stubSearchCriteria = $this->getMock(SearchCriteria::class);
    }

    public function testCountableInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\Countable::class, $this->filterCollection);
    }

    public function testIteratorAggregateInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\IteratorAggregate::class, $this->filterCollection);
    }

    public function testJsonSerializableInterfaceIsImplemented()
    {
        $this->assertInstanceOf(\JsonSerializable::class, $this->filterCollection);
    }

    public function testExceptionIsThrownDuringAttemptToAccessCollectionViaIteratorWithoutInitializingCollection()
    {
        $this->setExpectedException(FilterCollectionInNotInitializedException::class);
        $this->filterCollection->getIterator()->current();
    }

    public function testExceptionIsThrownDuringAttemptToAccessCollectionViaGetterWithoutInitializingCollection()
    {
        $this->setExpectedException(FilterCollectionInNotInitializedException::class);
        $this->filterCollection->getFilters();
    }

    public function testExceptionIsThrownDuringAttemptToRetrieveFiltersCountWithoutInitializingCollection()
    {
        $this->setExpectedException(FilterCollectionInNotInitializedException::class);
        count($this->filterCollection);
    }

    public function testEmptyCollectionIsReturnedIfNoFiltersAreApplicableToSearchDocumentCollection()
    {
        $selectedFilters = ['foo' => []];

        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection();

        $this->filterCollection->initialize(
            $stubSearchDocumentCollection,
            $this->stubSearchCriteria,
            $selectedFilters,
            $this->stubContext
        );

        $this->assertEmpty($this->filterCollection);
    }

    public function testCollectionIsAccessibleViaIterator()
    {
        $selectedFilters = ['foo' => []];

        $stubField = $this->createStubSearchDocumentField('foo', ['baz']);
        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields([$stubField]);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($stubSearchDocument);

        $this->filterCollection->initialize(
            $stubSearchDocumentCollection,
            $this->stubSearchCriteria,
            $selectedFilters,
            $this->stubContext
        );

        $this->assertCount(1, $this->filterCollection);
        $this->assertSame('foo', $this->filterCollection->getIterator()->current()->getCode());
    }

    public function testCollectionOnlyIncludesFiltersConfiguredForFilterNavigation()
    {
        $selectedFilters = ['foo' => []];

        $stubField1 = $this->createStubSearchDocumentField('foo', ['baz']);
        $stubField2 = $this->createStubSearchDocumentField('bar', ['qux']);

        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields([$stubField1, $stubField2]);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($stubSearchDocument);

        $this->filterCollection->initialize(
            $stubSearchDocumentCollection,
            $this->stubSearchCriteria,
            $selectedFilters,
            $this->stubContext
        );

        $this->assertCount(1, $this->filterCollection);

        $filters = $this->filterCollection->getFilters();

        $this->assertSame('foo', $filters[0]->getCode());
        $this->assertCount(1, $filters[0]->getOptionCollection());
        $this->assertSame('baz', $filters[0]->getOptionCollection()->getOptions()[0]->getValue());
        $this->assertSame(1, $filters[0]->getOptionCollection()->getOptions()[0]->getCount());
    }

    public function testFiltersInCollectionHasPreConfiguredOrder()
    {
        $selectedFilters = ['foo' => [], 'bar' => []];

        $stubField1 = $this->createStubSearchDocumentField('bar', ['qux']);
        $stubField2 = $this->createStubSearchDocumentField('foo', ['baz']);

        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields([$stubField1, $stubField2]);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($stubSearchDocument);

        $this->filterCollection->initialize(
            $stubSearchDocumentCollection,
            $this->stubSearchCriteria,
            $selectedFilters,
            $this->stubContext
        );

        $resultFilterCodes = array_map(function(FilterNavigationFilter $filter) {
            return $filter->getCode();
        }, $this->filterCollection->getFilters());

        $this->assertEquals(array_keys($selectedFilters), $resultFilterCodes);
    }

    public function testCollectionReflectsValuesFromSearchDocumentFieldsIfNoFiltersAreSelected()
    {
        $selectedFilters = ['foo' => [], 'bar' => []];

        $stubField = $this->createStubSearchDocumentField('foo', ['qux']);

        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields([$stubField]);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($stubSearchDocument);

        $this->filterCollection->initialize(
            $stubSearchDocumentCollection,
            $this->stubSearchCriteria,
            $selectedFilters,
            $this->stubContext
        );

        $this->assertCount(1, $this->filterCollection);

        $filters = $this->filterCollection->getFilters();

        $this->assertSame('foo', $filters[0]->getCode());
        $this->assertCount(1, $filters[0]->getOptionCollection());
        $this->assertSame('qux', $filters[0]->getOptionCollection()->getOptions()[0]->getValue());
        $this->assertSame(1, $filters[0]->getOptionCollection()->getOptions()[0]->getCount());
    }

    public function testOnlyFiltersWhichHaveMatchingValuesInProductsCollectionAreReturned()
    {
        $selectedFilters = ['foo' => ['baz'], 'bar' => []];

        $stubField = $this->createStubSearchDocumentField('foo', ['baz']);
        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields([$stubField]);
        $stubFilteredDocumentCollection = $this->createStubSearchDocumentCollection($stubSearchDocument);
        $stubUnfilteredDocumentCollection = $this->createStubSearchDocumentCollection($stubSearchDocument);

        $this->stubDataPoolReader->method('getSearchDocumentsMatchingCriteria')
            ->willReturn($stubUnfilteredDocumentCollection);

        $this->filterCollection->initialize(
            $stubFilteredDocumentCollection,
            $this->stubSearchCriteria,
            $selectedFilters,
            $this->stubContext
        );

        $this->assertCount(1, $this->filterCollection);

        $filters = $this->filterCollection->getFilters();

        $this->assertSame('foo', $filters[0]->getCode());
        $this->assertCount(1, $filters[0]->getOptionCollection());
        $this->assertSame('baz', $filters[0]->getOptionCollection()->getOptions()[0]->getValue());
        $this->assertSame(1, $filters[0]->getOptionCollection()->getOptions()[0]->getCount());
    }

    public function testSelectedFiltersHaveSiblingValuesForBroadeningProductsCollection()
    {
        $selectedFilters = ['foo' => ['baz'], 'bar' => ['0 Eur - 100 Eur']];

        $stubField1 = $this->createStubSearchDocumentField('foo', ['baz']);
        $stubField2 = $this->createStubSearchDocumentField('foo', ['qux']);
        $stubField3 = $this->createStubSearchDocumentField('bar', ['0 Eur - 100 Eur']);
        $stubField4 = $this->createStubSearchDocumentField('bar', ['100 Eur - 200 Eur']);

        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields(
            [$stubField1, $stubField2, $stubField3, $stubField4]
        );

        $stubFilteredDocumentCollection = $this->createStubSearchDocumentCollection($stubSearchDocument);
        $stubUnfilteredDocumentCollection = $this->createStubSearchDocumentCollection($stubSearchDocument);

        $this->stubDataPoolReader->method('getSearchDocumentsMatchingCriteria')
            ->willReturn($stubUnfilteredDocumentCollection);

        $stubSearchCriteria = $this->getMock(SearchCriteria::class);
        $this->stubSearchCriteriaBuilder->method('fromRequestParameter')->willReturn($stubSearchCriteria);

        $this->filterCollection->initialize(
            $stubFilteredDocumentCollection,
            $this->stubSearchCriteria,
            $selectedFilters,
            $this->stubContext
        );

        $this->assertCount(2, $this->filterCollection);

        $filters = $this->filterCollection->getFilters();

        $this->assertSame('foo', $filters[0]->getCode());
        $this->assertCount(2, $filters[0]->getOptionCollection());
        $this->assertSame('baz', $filters[0]->getOptionCollection()->getOptions()[0]->getValue());
        $this->assertSame(1, $filters[0]->getOptionCollection()->getOptions()[0]->getCount());
        $this->assertSame('qux', $filters[0]->getOptionCollection()->getOptions()[1]->getValue());
        $this->assertSame(1, $filters[0]->getOptionCollection()->getOptions()[1]->getCount());

        $this->assertSame('bar', $filters[1]->getCode());
        $this->assertCount(2, $filters[1]->getOptionCollection());
        $this->assertSame('0 Eur - 100 Eur', $filters[1]->getOptionCollection()->getOptions()[0]->getValue());
        $this->assertSame(1, $filters[1]->getOptionCollection()->getOptions()[0]->getCount());
        $this->assertSame('100 Eur - 200 Eur', $filters[1]->getOptionCollection()->getOptions()[1]->getValue());
        $this->assertSame(1, $filters[1]->getOptionCollection()->getOptions()[1]->getCount());
    }
    
    public function testArrayRepresentationOfFilterNavigationIsReturned()
    {
        $selectedFilters = ['foo' => []];

        $stubField1 = $this->createStubSearchDocumentField('foo', ['baz']);
        $stubField2 = $this->createStubSearchDocumentField('bar', ['qux']);

        $stubSearchDocument = $this->createStubSearchDocumentWithGivenFields([$stubField1, $stubField2]);
        $stubSearchDocumentCollection = $this->createStubSearchDocumentCollection($stubSearchDocument);

        $this->filterCollection->initialize(
            $stubSearchDocumentCollection,
            $this->stubSearchCriteria,
            $selectedFilters,
            $this->stubContext
        );

        $result = $this->filterCollection->jsonSerialize();

        $this->assertInternalType('array', $result);
        $this->assertCount(1, $result);
    }
}
