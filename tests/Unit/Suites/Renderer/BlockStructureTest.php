<?php


namespace Brera\Renderer;

/**
 * @covers \Brera\Renderer\BlockStructure
 */
class BlockStructureTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BlockStructure
     */
    private $blockStructure;

    protected function setUp()
    {
        $this->blockStructure = new BlockStructure();
    }

    /**
     * @return Block|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getStubBlock()
    {
        return $this->getMock(Block::class, [], [], '', false);
    }

    /**
     * @param string $blockName
     * @return Block|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getStubBlockWithName($blockName)
    {
        $stubBlock = $this->getStubBlock();
        $stubBlock->expects($this->any())
            ->method('getBlockName')
            ->willReturn($blockName);
        return $stubBlock;
    }
    
    private function assertParentHasChild($parentName, $childName)
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

    /**
     * @test
     */
    public function itShouldBePossibleToAddABlock()
    {
        $stubBlock = $this->getStubBlock();
        $this->blockStructure->addBlock($stubBlock);
        $this->assertAttributeContains($stubBlock, 'blocks', $this->blockStructure);
    }

    /**
     * @test
     * @expectedException \Brera\Renderer\BlockDoesNotExistException
     */
    public function itShouldThrowAnExceptionWhenSpecifyingAnUnknownParentForABlock()
    {
        $this->blockStructure->setParentBlock('unknown-parent', $this->getStubBlock());
    }

    /**
     * @test
     */
    public function itShouldSetTheParentForAGivenBlock()
    {
        $stubParent = $this->getStubBlockWithName('parent');
        $stubChild = $this->getStubBlockWithName('child');
        $this->blockStructure->addBlock($stubParent);
        $this->blockStructure->setParentBlock('parent', $stubChild);
        
        $this->assertParentHasChild('parent', 'child');
    }

    /**
     * @test
     */
    public function itShouldReturnFalseForUnknownBlocks()
    {
        $this->assertFalse($this->blockStructure->hasBlock('unknown-block'));
    }

    /**
     * @test
     */
    public function itShouldReturnTrueForKnownBlocks()
    {
        $blockName = 'test';
        $this->blockStructure->addBlock($this->getStubBlockWithName($blockName));
        $this->assertTrue($this->blockStructure->hasBlock($blockName));
    }

    /**
     * @test
     */
    public function itShouldReturnFalseForUnknownParentBlock()
    {
        $parentName = 'parent';
        $childName = 'child';
        $this->assertFalse($this->blockStructure->hasChildBlock($parentName, $childName));
    }

    /**
     * @test
     */
    public function itShouldReturnFalseForUnknownChildBlocks()
    {
        $parentName = 'parent';
        $childName = 'child';
        $this->blockStructure->addBlock($this->getStubBlockWithName($parentName));
        $this->blockStructure->setParentBlock($parentName, $this->getStubBlockWithName('another-child'));
        $this->assertFalse($this->blockStructure->hasChildBlock($parentName, $childName));
    }

    /**
     * @test
     */
    public function itShouldReturnTrueIfTheChildIsSetForTheParent()
    {
        $parentName = 'parent';
        $childName = 'child';
        $this->blockStructure->addBlock($this->getStubBlockWithName($parentName));
        $this->blockStructure->setParentBlock($parentName, $this->getStubBlockWithName($childName));
        $this->assertTrue($this->blockStructure->hasChildBlock($parentName, $childName));
    }

    /**
     * @test
     * @expectedException \Brera\Renderer\BlockDoesNotExistException
     * @expectedExceptionMessage Block does not exist:
     */
    public function itShouldThrowAnExceptionForUnknownBlocks()
    {
        $this->blockStructure->getBlock('unknown-block');
    }

    /**
     * @test
     */
    public function itShouldReturnTheSpecifiedBlock()
    {
        $blockName = 'block1';
        $stubBlock = $this->getStubBlockWithName($blockName);
        $this->blockStructure->addBlock($stubBlock);
        $this->blockStructure->addBlock($this->getStubBlockWithName('block2'));
        
        $this->assertSame($stubBlock, $this->blockStructure->getBlock($blockName));
    }

    /**
     * @test
     * @expectedException \Brera\Renderer\BlockIsNotAChildOfParentBlockException
     * @expectedExceptionMessage The block "child" is not a child of the parent block "parent"
     */
    public function itShouldThrowAnExceptionIfTheParentBlockHasNoChildren()
    {
        $parentName = 'parent';
        $childName = 'child';
        $this->blockStructure->getChildBlock($parentName, $childName);
    }

    /**
     * @test
     * @expectedException \Brera\Renderer\BlockIsNotAChildOfParentBlockException
     * @expectedExceptionMessage The block "child" is not a child of the parent block "parent"
     */
    public function itShouldThrowAnExceptionIfAChildBlockIsNotSetForAParent()
    {
        $parentName = 'parent';
        $childName = 'child';
        $this->blockStructure->addBlock($this->getStubBlockWithName($parentName));
        $this->blockStructure->getChildBlock($parentName, $childName);
    }

    /**
     * @test
     */
    public function itShouldReturnTheChildBlock()
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
