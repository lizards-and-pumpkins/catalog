<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\TemplateRendering;

use LizardsAndPumpkins\Import\TemplateRendering\Exception\BlockDoesNotExistException;
use LizardsAndPumpkins\Import\TemplateRendering\Exception\BlockIsNotAChildOfParentBlockException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\TemplateRendering\BlockStructure
 */
class BlockStructureTest extends TestCase
{
    /**
     * @var BlockStructure
     */
    private $blockStructure;

    private function assertParentHasChild(string $parentName, string $childName): void
    {
        $property = new \ReflectionProperty($this->blockStructure, 'blockChildren');
        $property->setAccessible(true);
        $childBlocks = $property->getValue($this->blockStructure);
        $this->assertArrayHasKey(
            $parentName,
            $childBlocks,
            sprintf('No children blocks set for parent "%s"', $parentName)
        );
        $this->assertTrue(
            in_array($childName, $childBlocks[$parentName]),
            sprintf('The child block "%s" is not set for the parent block "%s"', $childName, $parentName)
        );
    }

    final protected function setUp(): void
    {
        $this->blockStructure = new BlockStructure();
    }

    public function testBlockCanBeAdded(): void
    {
        $stubBlock = $this->createMock(Block::class);
        $stubBlock->method('getBlockName')->willReturn('foo');

        $this->blockStructure->addBlock($stubBlock);

        $this->assertTrue($this->blockStructure->hasBlock('foo'));
    }

    public function testExceptionIsThrownIfUnknownBlockParentIsSpecified(): void
    {
        $this->expectException(BlockDoesNotExistException::class);

        $this->blockStructure->setParentBlock('unknown-parent', $this->createMock(Block::class));
    }

    public function testBlockParentIsSet(): void
    {
        $parentName = 'parent';
        $stubParent = $this->createMock(Block::class);
        $stubParent->method('getBlockName')->willReturn($parentName);

        $childName = 'child';
        $stubChild = $this->createMock(Block::class);
        $stubChild->method('getBlockName')->willReturn($childName);

        $this->blockStructure->addBlock($stubParent);
        $this->blockStructure->setParentBlock($parentName, $stubChild);

        $this->assertParentHasChild($parentName, $childName);
    }

    public function testFalseIsReturnedForUnknownBlocks(): void
    {
        $this->assertFalse($this->blockStructure->hasBlock('unknown-block'));
    }

    public function testFalseIsReturnedForUnknownParentBlock(): void
    {
        $parentName = 'parent';
        $childName = 'child';
        $this->assertFalse($this->blockStructure->hasChildBlock($parentName, $childName));
    }

    public function testFalseIsReturnedForUnknownChildBlocks(): void
    {
        $parentName = 'parent';
        $stubParent = $this->createMock(Block::class);
        $stubParent->method('getBlockName')->willReturn($parentName);

        $childName = 'child';
        $stubChild = $this->createMock(Block::class);
        $stubChild->method('getBlockName')->willReturn($childName);

        $this->blockStructure->addBlock($stubParent);
        $this->blockStructure->setParentBlock($parentName, $stubChild);

        $this->assertFalse($this->blockStructure->hasChildBlock($parentName, 'non-existing-child'));
    }

    public function testTrueIsReturnedIfChildIsSetForParent(): void
    {
        $parentName = 'parent';
        $stubParent = $this->createMock(Block::class);
        $stubParent->method('getBlockName')->willReturn($parentName);

        $childName = 'child';
        $stubChild = $this->createMock(Block::class);
        $stubChild->method('getBlockName')->willReturn($childName);

        $this->blockStructure->addBlock($stubParent);
        $this->blockStructure->setParentBlock($parentName, $stubChild);

        $this->assertTrue($this->blockStructure->hasChildBlock($parentName, $childName));
    }

    public function testExceptionIsThrownForUnknownBlocks(): void
    {
        $this->expectException(BlockDoesNotExistException::class);
        $this->expectExceptionMessage('Block does not exist:');
        $this->blockStructure->getBlock('unknown-block');
    }

    public function testSpecifiedBlockIsReturned(): void
    {
        $block1Name = 'block1';
        $stubBlock1 = $this->createMock(Block::class);
        $stubBlock1->method('getBlockName')->willReturn($block1Name);

        $block1Name = 'block2';
        $stubBlock2 = $this->createMock(Block::class);
        $stubBlock2->method('getBlockName')->willReturn($block1Name);

        $this->blockStructure->addBlock($stubBlock1);
        $this->blockStructure->addBlock($stubBlock2);

        $this->assertEquals($stubBlock1, $this->blockStructure->getBlock($block1Name));
    }

    public function testExceptionIsThrownIfParentBlockHasNoChildren(): void
    {
        $parentName = 'parent';
        $childName = 'child';

        $this->expectException(BlockIsNotAChildOfParentBlockException::class);
        $this->expectExceptionMessage('The block "child" is not a child of the parent block "parent"');

        $this->blockStructure->getChildBlock($parentName, $childName);
    }

    public function testExceptionIsThrownIfChildBlockIsNotSetForParent(): void
    {
        $parentName = 'parent';
        $stubParent = $this->createMock(Block::class);
        $stubParent->method('getBlockName')->willReturn($parentName);

        $childName = 'child';

        $this->blockStructure->addBlock($stubParent);

        $this->expectException(BlockIsNotAChildOfParentBlockException::class);
        $this->expectExceptionMessage('The block "child" is not a child of the parent block "parent"');

        $this->blockStructure->getChildBlock($parentName, $childName);
    }

    public function testChildBlockIsReturned(): void
    {
        $parentName = 'parent';
        $stubParent = $this->createMock(Block::class);
        $stubParent->method('getBlockName')->willReturn($parentName);

        $childName = 'child';
        $stubChild = $this->createMock(Block::class);
        $stubChild->method('getBlockName')->willReturn($childName);

        $this->blockStructure->addBlock($stubParent);
        $this->blockStructure->addBlock($stubChild);
        $this->blockStructure->setParentBlock($parentName, $stubChild);

        $this->assertSame($stubChild, $this->blockStructure->getChildBlock($parentName, $childName));
    }
}
