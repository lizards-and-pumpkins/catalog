<?php

namespace Brera\DataPool\SearchEngine\SearchDocument;

/**
 * @covers \Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection
 * @uses   \Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentField
 */
class SearchDocumentFieldCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testCollectionIsCreatedFromArray()
    {
        $fieldsArray = ['foo' => 'bar', 'baz' => 'qux'];
        $collection = SearchDocumentFieldCollection::fromArray($fieldsArray);
        $result = $collection->getFields();

        $this->assertCount(2, $result);
        $this->assertContainsOnly(SearchDocumentField::class, $result);
        $this->assertEquals('foo', $result[0]->getKey());
        $this->assertEquals('bar', $result[0]->getValue());
        $this->assertEquals('baz', $result[1]->getKey());
        $this->assertEquals('qux', $result[1]->getValue());
    }

    public function testEmptyCollectionIsCreatedFromEmptyArray()
    {
        $collection = SearchDocumentFieldCollection::fromArray([]);

        $this->assertInstanceOf(SearchDocumentFieldCollection::class, $collection);
        $this->assertCount(0, $collection->getFields());
    }

    public function testFalseIsReturnedIfNoMatchingFieldIsPresent()
    {
        $collection = SearchDocumentFieldCollection::fromArray([]);
        $fieldToCheck = SearchDocumentField::fromKeyAndValue('test-field-name', 'test-field-value');
        $this->assertFalse($collection->contains($fieldToCheck));
    }

    public function testTrueIsReturnedIfAMatchingFieldIsPresent()
    {

        $testFieldName = 'test-field-name';
        $testFieldValue = 'test-field-value';
        $collection = SearchDocumentFieldCollection::fromArray([$testFieldName => $testFieldValue]);
        $fieldToCheck = SearchDocumentField::fromKeyAndValue($testFieldName, $testFieldValue);
        $this->assertTrue($collection->contains($fieldToCheck));
    }

    public function testArrayRepresentationOfSearchDocumentFieldCollectionIsReturned()
    {
        $searchFieldData = ['test-field-name' => 'test-field-value'];
        $collection = SearchDocumentFieldCollection::fromArray($searchFieldData);
        $this->assertSame($searchFieldData, $collection->toArray());
    }
}
