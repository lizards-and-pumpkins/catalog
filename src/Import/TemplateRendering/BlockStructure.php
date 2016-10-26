<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\TemplateRendering;

use LizardsAndPumpkins\Import\TemplateRendering\Exception\BlockDoesNotExistException;
use LizardsAndPumpkins\Import\TemplateRendering\Exception\BlockIsNotAChildOfParentBlockException;

class BlockStructure
{
    /**
     * @var Block[]
     */
    private $blocks = [];

    /**
     * @var array[]
     */
    private $blockChildren = [];

    public function addBlock(Block $blockInstance)
    {
        $this->blocks[$blockInstance->getBlockName()] = $blockInstance;
    }

    public function setParentBlock(string $parentName, Block $childBlockInstance)
    {
        if (!$this->hasBlock($parentName)) {
            throw new BlockDoesNotExistException(sprintf(
                'Can\'t set the parent for child block "%s": parent block "%s" is unknown',
                $childBlockInstance->getBlockName(),
                $parentName
            ));
        }
        if (! $this->hasChildBlock($parentName, $childBlockInstance->getBlockName())) {
            $this->blockChildren[$parentName][] = $childBlockInstance->getBlockName();
        }
    }

    public function hasBlock(string $blockName) : bool
    {
        return array_key_exists($blockName, $this->blocks);
    }

    public function hasChildBlock(string $parentName, string $childName) : bool
    {
        if (!array_key_exists($parentName, $this->blockChildren)) {
            return false;
        }
        if (!in_array($childName, $this->blockChildren[$parentName])) {
            return false;
        }
        return true;
    }

    public function getBlock(string $blockName) : Block
    {
        if (!array_key_exists($blockName, $this->blocks)) {
            throw new BlockDoesNotExistException(sprintf('Block does not exist: "%s"', $blockName));
        }
        return $this->blocks[$blockName];
    }

    public function getChildBlock(string $parentName, string $childName) : Block
    {
        if (!$this->hasChildBlock($parentName, $childName)) {
            throw new BlockIsNotAChildOfParentBlockException(sprintf(
                'The block "%s" is not a child of the parent block "%s"',
                $childName,
                $parentName
            ));
        }
        return $this->getBlock($childName);
    }
}
