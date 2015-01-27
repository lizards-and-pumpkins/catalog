<?php

namespace Brera\Renderer;

use Brera\ProjectionSourceData;

class Block
{
    /**
     * @var string
     */
    private $template;

    /**
     * @var Block[]
     */
    private $children = [];

    public function __construct($template, ProjectionSourceData $dataObject)
    {
        $this->template = $template;
    }

    /**
     * @return string
     * @throws TemplateFileNotReadableException
     */
    public final function render()
    {
        $templatePath = realpath($this->template);

        if (!is_readable($templatePath) || is_dir($templatePath)) {
            throw new TemplateFileNotReadableException();
        }

        ob_start();

        include $templatePath;

        return ob_get_clean();
    }

    /**
     * @param string $blockNameInLayout
     * @param Block $block
     * @return null
     */
    public function addChildBlock($blockNameInLayout, Block $block)
    {
        $this->children[$blockNameInLayout] = $block;
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
