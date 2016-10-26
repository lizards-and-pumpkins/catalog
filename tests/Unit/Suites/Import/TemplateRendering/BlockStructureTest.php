<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\TemplateRendering;

use LizardsAndPumpkins\Import\TemplateRendering\Exception\BlockDoesNotExistException;
use LizardsAndPumpkins\Import\TemplateRendering\Exception\BlockIsNotAChildOfParentBlockException;

/**
 * @covers \LizardsAndPumpkins\Import\TemplateRendering\BlockStructure
 */
class BlockStructureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BlockStructure
     */
    private $blockStructure;

    /**
     * @return Block|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getStubBlock() : Block
    {
        return $this->createMock(Block::class);
    }

    /**
     * @param string $blockName
     * @return Block|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getStubBlockWithName(string $blockName) : Block
    {
        $stubBlock = $this->getStubBlock();
        $stubBlock->method('getBlockName')->willReturn($blockName);
        return $stubBlock;
    }

    private function assertParentHasChild(string $parentName, string $childName)
    {
        $property = new \ReflectionProperty($this->blockStructure, 'blockChildren');
        $property->setAccessible(true);
        $childBlocks = $property->getValue($this->blockStructure);
        $this->assertTrue(
            array_key_exists($parentName, $childBlocks),
            sprintf('No children blocks set for parent "%s"', $parentName)
        );
        $this->assertContains(
            $childName,
            $childBlocks[$parentName],
            sprintf('The child block "%s" is not set for the parent block "%s"', $childName, $parentName)
        );
    }

    protected function setUp()
    {
        $this->blockStructure = new BlockStructure();
    }

    public function testBlockCanBeAdded()
    {
        $stubBlock = $this->getStubBlock();
        $this->blockStructure->addBlock($stubBlock);
        $this->assertAttributeContains($stubBlock, 'blocks', $this->blockStructure);
    }

    public function testExceptionIsThrownIfUnknownBlockParentIsSpecified()
    {
        $this->expectException(BlockDoesNotExistException::class);
        $this->blockStructure->setParentBlock('unknown-parent', $this->getStubBlock());
    }

    public function testBlockParentIsSet()
    {
        $stubParent = $this->getStubBlockWithName('parent');
        $stubChild = $this->getStubBlockWithName('child');
        $this->blockStructure->addBlock($stubParent);
        $this->blockStructure->setParentBlock('parent', $stubChild);

        $this->assertParentHasChild('parent', 'child');
    }

    public function testFalseIsReturnedForUnknownBlocks()
    {
        $this->assertFalse($this->blockStructure->hasBlock('unknown-block'));
    }

    public function testTrueIsReturnedForKnownBlocks()
    {
        $blockName = 'test';
        $this->blockStructure->addBlock($this->getStubBlockWithName($blockName));
        $this->assertTrue($this->blockStructure->hasBlock($blockName));
    }

    public function testFalseIsReturnedForUnknownParentBlock()
    {
        $parentName = 'parent';
        $childName = 'child';
        $this->assertFalse($this->blockStructure->hasChildBlock($parentName, $childName));
    }

    public function testFalseIsReturnedForUnknownChildBlocks()
    {
        $parentName = 'parent';
        $childName = 'child';
        $this->blockStructure->addBlock($this->getStubBlockWithName($parentName));
        $this->blockStructure->setParentBlock($parentName, $this->getStubBlockWithName('another-child'));
        $this->assertFalse($this->blockStructure->hasChildBlock($parentName, $childName));
    }

    public function testTrueIsReturnedIfChildIsSetForParent()
    {
        $parentName = 'parent';
        $childName = 'child';
        $this->blockStructure->addBlock($this->getStubBlockWithName($parentName));
        $this->blockStructure->setParentBlock($parentName, $this->getStubBlockWithName($childName));
        $this->assertTrue($this->blockStructure->hasChildBlock($parentName, $childName));
    }

    public function testExceptionIsThrownForUnknownBlocks()
    {
        $this->expectException(BlockDoesNotExistException::class);
        $this->expectExceptionMessage('Block does not exist:');
        $this->blockStructure->getBlock('unknown-block');
    }

    public function testSpecifiedBlockIsReturned()
    {
        $blockName = 'block1';
        $stubBlock = $this->getStubBlockWithName($blockName);
        $this->blockStructure->addBlock($stubBlock);
        $this->blockStructure->addBlock($this->getStubBlockWithName('block2'));

        $this->assertSame($stubBlock, $this->blockStructure->getBlock($blockName));
    }

    public function testExceptionIsThrownIfParentBlockHasNoChildren()
    {
        $parentName = 'parent';
        $childName = 'child';

        $this->expectException(BlockIsNotAChildOfParentBlockException::class);
        $this->expectExceptionMessage('The block "child" is not a child of the parent block "parent"');

        $this->blockStructure->getChildBlock($parentName, $childName);
    }

    public function testExceptionIsThrownIfChildBlockIsNotSetForParent()
    {
        $parentName = 'parent';
        $childName = 'child';
        $this->blockStructure->addBlock($this->getStubBlockWithName($parentName));

        $this->expectException(BlockIsNotAChildOfParentBlockException::class);
        $this->expectExceptionMessage('The block "child" is not a child of the parent block "parent"');

        $this->blockStructure->getChildBlock($parentName, $childName);
    }

    public function testChildBlockIsReturned()
    {
        $parentName = 'parent';
        $childName = 'child';
        $stubChildBlock = $this->getStubBlockWithName($childName);
        $this->blockStructure->addBlock($this->getStubBlockWithName($parentName));
        $this->blockStructure->addBlock($stubChildBlock);
        $this->blockStructure->setParentBlock($parentName, $stubChildBlock);
        $this->assertSame($stubChildBlock, $this->blockStructure->getChildBlock($parentName, $childName));
    }
}
