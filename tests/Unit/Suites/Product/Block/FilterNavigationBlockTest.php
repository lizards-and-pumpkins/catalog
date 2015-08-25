<?php

namespace Brera\Product\Block;

use Brera\DataPool\SearchEngine\SearchDocument\SearchDocument;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentCollection;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentField;
use Brera\DataPool\SearchEngine\SearchDocument\SearchDocumentFieldCollection;
use Brera\Renderer\Block;
use Brera\Renderer\BlockRenderer;
use Brera\Renderer\InvalidDataObjectException;

/**
 * @covers \Brera\Product\Block\FilterNavigationBlock
 * @uses   \Brera\Renderer\Block
 */
class FilterNavigationBlockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchDocumentCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubSearchDocumentCollection;

    /**
     * @var FilterNavigationBlock
     */
    private $block;

    /**
     * @var BlockRenderer|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stubBlockRenderer;

    protected function setUp()
    {
        $this->stubBlockRenderer = $this->getMock(BlockRenderer::class, [], [], '', false);
        $this->stubSearchDocumentCollection = $this->getMock(SearchDocumentCollection::class, [], [], '', false);

        $stubDataObject = [
            'search_document_collection'        => $this->stubSearchDocumentCollection,
            'filter_navigation_attribute_codes' => ['foo', 'qux']
        ];

        $this->block = new FilterNavigationBlock($this->stubBlockRenderer, 'foo.phtml', 'foo', $stubDataObject);
    }

    public function testBlockClassIsExtended()
    {
        $this->assertInstanceOf(Block::class, $this->block);
    }

    public function testExceptionIsThrownIfDataObjectIsNotAnArray()
    {
        $this->setExpectedException(InvalidDataObjectException::class);

        $invalidDataObject = '';
        (new FilterNavigationBlock($this->stubBlockRenderer, 'foo.phtml', 'foo', $invalidDataObject))->getFilters();
    }

    public function testExceptionIsThrownIfDataObjectArrayDoesNotContainSearchDocumentCollectionNode()
    {
        $this->setExpectedException(InvalidDataObjectException::class);
        $invalidDataObject = ['filter_navigation_attribute_codes' => []];
        (new FilterNavigationBlock($this->stubBlockRenderer, 'foo.phtml', 'foo', $invalidDataObject))->getFilters();
    }

    public function testExceptionIsThrownIfSearchDocumentCollectionNodeOfDataObjectHasWrongType()
    {
        $this->setExpectedException(InvalidDataObjectException::class);

        $invalidDataObject = [
            'search_document_collection'        => new \stdClass,
            'filter_navigation_attribute_codes' => []
        ];
        (new FilterNavigationBlock($this->stubBlockRenderer, 'foo.phtml', 'foo', $invalidDataObject))->getFilters();
    }

    public function testExceptionIsThrownIfDataObjectArrayDoesNotContainFilterNavigationAttributeCodesNode()
    {
        $this->setExpectedException(InvalidDataObjectException::class);

        $this->stubSearchDocumentCollection->method('getDocuments')->willReturn([]);
        $invalidDataObject = ['search_document_collection' => $this->stubSearchDocumentCollection];
        (new FilterNavigationBlock($this->stubBlockRenderer, 'foo.phtml', 'foo', $invalidDataObject))->getFilters();
    }

    public function testExceptionIsThrownIfFilterNavigationAttributeCodesNodeOfDataObjectArrayIsNotAnArray()
    {
        $this->setExpectedException(InvalidDataObjectException::class);

        $this->stubSearchDocumentCollection->method('getDocuments')->willReturn([]);
        $invalidDataObject = [
            'search_document_collection'        => $this->stubSearchDocumentCollection,
            'filter_navigation_attribute_codes' => new \stdClass
        ];
        (new FilterNavigationBlock($this->stubBlockRenderer, 'foo.phtml', 'foo', $invalidDataObject))->getFilters();
    }

    public function testArrayRepresentationOfFiltersIsReturned()
    {
        $stubField1 = $this->createStubSearchDocumentField('foo', 'bar');
        $stubField2 = $this->createStubSearchDocumentField('foo', 'baz');
        $stubField3 = $this->createStubSearchDocumentField('qux', '0 Eur - 100 Eur');

        $stubSearchDocumentA = $this->createStubSearchDocumentWithGivenFields([$stubField1, $stubField2, $stubField3]);
        $stubSearchDocumentB = $this->createStubSearchDocumentWithGivenFields([$stubField1]);

        $this->stubSearchDocumentCollection->method('getDocuments')
            ->willReturn([$stubSearchDocumentA, $stubSearchDocumentB]);

        $result = $this->block->getFilters();
        $expectation = [
            'foo' => [
                'bar' => 2,
                'baz' => 1,
            ],
            'qux' => [
                '0 Eur - 100 Eur' => 1,
            ]
        ];

        $this->assertEquals($expectation, $result);
    }

    public function testOnlyAttributesConfiguredToBeIncludedIntoFilterNavigationAreReturned()
    {
        $stubField1 = $this->createStubSearchDocumentField('foo', 'bar');
        $stubField2 = $this->createStubSearchDocumentField('baz', 'qux');

        $stubSearchDocumentA = $this->createStubSearchDocumentWithGivenFields([$stubField1, $stubField2]);
        $stubSearchDocumentB = $this->createStubSearchDocumentWithGivenFields([$stubField1]);

        $this->stubSearchDocumentCollection->method('getDocuments')
            ->willReturn([$stubSearchDocumentA, $stubSearchDocumentB]);

        $result = $this->block->getFilters();
        $expectation = [
            'foo' => [
                'bar' => 2,
            ]
        ];

        $this->assertEquals($expectation, $result);
    }

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
}
