<?php

namespace Brera\DataPool\SearchEngine\SearchDocument;

/**
 * @covers \Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection
 * @uses   \Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentField
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

    public function testCollectionIsAccessibleViaGetter()
    {
        $fieldsArray = ['foo' => 'bar', 'baz' => 'qux'];
        $collection = SearchDocumentFieldCollection::fromArray($fieldsArray);
        $result = $collection->getFields();

        $this->assertCount(2, $collection);
        $this->assertContainsOnly(SearchDocumentField::class, $result);
        $this->assertEquals('foo', $result[0]->getKey());
        $this->assertEquals('bar', $result[0]->getValue());
        $this->assertEquals('baz', $result[1]->getKey());
        $this->assertEquals('qux', $result[1]->getValue());
    }

    public function testCollectionIsAccessibleViaIterator()
    {
        $fieldsArray = ['foo' => 'bar'];
        $collection = SearchDocumentFieldCollection::fromArray($fieldsArray);

        $this->assertCount(1, $collection);
        $this->assertContainsOnly(SearchDocumentField::class, $collection);
        $this->assertEquals('foo', $collection->getIterator()->current()->getKey());
        $this->assertEquals('bar', $collection->getIterator()->current()->getValue());
    }
}
