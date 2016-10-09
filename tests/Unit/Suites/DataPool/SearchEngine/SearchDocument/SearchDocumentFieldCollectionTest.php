<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchDocument\SearchDocumentField
 */
class SearchDocumentFieldCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testCountableInterfaceIsImplemented()
    {
        $collection = SearchDocumentFieldCollection::fromArray([]);
        $this->assertInstanceOf(\Countable::class, $collection);
    }

    public function testIteratorAggregateInterfaceIsImplemented()
    {
        $collection = SearchDocumentFieldCollection::fromArray([]);
        $this->assertInstanceOf(\IteratorAggregate::class, $collection);
    }

    public function testItShouldConvertStringValuesIntoArrays()
    {
        $fieldsArray = ['foo' => 'bar'];
        $collection = SearchDocumentFieldCollection::fromArray($fieldsArray);
        $this->assertSame(['bar'], $collection->getFields()['foo']->getValues());
    }

    public function testCollectionIsAccessibleViaGetter()
    {
        $fieldsArray = ['foo' => 'bar', 'baz' => 'qux'];
        $collection = SearchDocumentFieldCollection::fromArray($fieldsArray);
        $result = $collection->getFields();

        $this->assertCount(2, $collection);
        $this->assertContainsOnly(SearchDocumentField::class, $result);
        $this->assertEquals('foo', $result['foo']->getKey());
        $this->assertEquals(['bar'], $result['foo']->getValues());
        $this->assertEquals('baz', $result['baz']->getKey());
        $this->assertEquals(['qux'], $result['baz']->getValues());
    }

    public function testCollectionIsAccessibleViaIterator()
    {
        $fieldsArray = ['foo' => 'bar'];
        $collection = SearchDocumentFieldCollection::fromArray($fieldsArray);

        $this->assertCount(1, $collection);
        $this->assertContainsOnly(SearchDocumentField::class, $collection);
        $this->assertEquals('foo', $collection->getIterator()->current()->getKey());
        $this->assertEquals(['bar'], $collection->getIterator()->current()->getValues());
    }
}
