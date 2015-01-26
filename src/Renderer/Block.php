<?php

namespace Brera\Renderer;

use Brera\ProjectionSourceData;

class Block
{
    /**
     * @var Layout
     */
    private $layout;

    /**
     * @var Block[]
     */
    private $children = [];

    public function __construct(Layout $layout, ProjectionSourceData $dataObject)
    {
        $this->layout = $layout;
    }

    /**
     * @return string
     * @throws TemplateFileNotReadableException
     */
    public final function render()
    {
        $templatePath = realpath($this->layout->getAttribute('template'));

        if (!is_readable($templatePath) || is_dir($templatePath)) {
            throw new TemplateFileNotReadableException();
        }

        ob_start();

        include $templatePath;

        return ob_get_clean();
    }

    /**
     * @param Block $block
     * @return null
     */
    public function addChildBlock(Block $block)
    {
        $this->children[$block->layout->getAttribute('name')] = $block;
    }

    /**
     * @param $blockName
     * @return string
     */
    public function getChildBlock($blockName)
    {
        if (!array_key_exists($blockName, $this->children)) {
            return '';
        }

        return $this->children[$blockName]->render();
    }
}
