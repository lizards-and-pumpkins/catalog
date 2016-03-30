<?php

namespace LizardsAndPumpkins\Import\TemplateRendering;

use LizardsAndPumpkins\Import\ContentBlock\Block;
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

    /**
     * @param string $parentName
     * @param Block $childBlockInstance
     */
    public function setParentBlock($parentName, Block $childBlockInstance)
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

    /**
     * @param string $blockName
     * @return bool
     */
    public function hasBlock($blockName)
    {
        return array_key_exists($blockName, $this->blocks);
    }

    /**
     * @param string $parentName
     * @param string $childName
     * @return bool
     */
    public function hasChildBlock($parentName, $childName)
    {
        if (!array_key_exists($parentName, $this->blockChildren)) {
            return false;
        }
        if (!in_array($childName, $this->blockChildren[$parentName])) {
            return false;
        }
        return true;
    }

    /**
     * @param string $blockName
     * @return Block
     */
    public function getBlock($blockName)
    {
        if (!array_key_exists($blockName, $this->blocks)) {
            throw new BlockDoesNotExistException(sprintf('Block does not exist: "%s"', $blockName));
        }
        return $this->blocks[$blockName];
    }

    /**
     * @param string $parentName
     * @param string $childName
     * @return string
     */
    public function getChildBlock($parentName, $childName)
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
