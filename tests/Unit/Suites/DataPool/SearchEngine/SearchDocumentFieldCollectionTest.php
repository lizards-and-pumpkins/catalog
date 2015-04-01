<?php

namespace Brera\DataPool\SearchEngine;

/**
 * @covers \Brera\DataPool\SearchEngine\SearchDocumentFieldCollection
 * @uses   \Brera\DataPool\SearchEngine\SearchDocumentField
 */
class SearchDocumentFieldCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function itShouldCreateCollectionFromArray()
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

    /**
     * @test
     */
    public function itShouldCreateAnEmptyCollectionFromEmptyArray()
    {
        $collection = SearchDocumentFieldCollection::fromArray([]);

        $this->assertInstanceOf(SearchDocumentFieldCollection::class, $collection);
        $this->assertCount(0, $collection->getFields());
    }

    /**
     * @test
     */
    public function itShouldReturnFalseIfNoMatchingFieldIsPresent()
    {
        $collection = SearchDocumentFieldCollection::fromArray([]);
        $fieldToCheck = SearchDocumentField::fromKeyAndValue('test-field-name', 'test-field-value');
        $this->assertFalse($collection->contains($fieldToCheck));
    }

    /**
     * @test
     */
    public function itShouldReturnTrueIfAMatchingFieldIsPresent()
    {

        $testFieldName = 'test-field-name';
        $testFieldValue = 'test-field-value';
        $collection = SearchDocumentFieldCollection::fromArray([$testFieldName => $testFieldValue]);
        $fieldToCheck = SearchDocumentField::fromKeyAndValue($testFieldName, $testFieldValue);
        $this->assertTrue($collection->contains($fieldToCheck));
    }
}
