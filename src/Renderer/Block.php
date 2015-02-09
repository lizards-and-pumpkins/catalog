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
     * @var ProjectionSourceData
     */
    private $dataObject;

    /**
     * @var Block[]
     */
    private $children = [];

    final public function __construct($template, ProjectionSourceData $dataObject)
    {
        $this->template = $template;
        $this->dataObject = $dataObject;
    }

    /**
     * @return ProjectionSourceData
     */
    final protected function getDataObject()
    {
        return $this->dataObject;
    }

    /**
     * @return string
     * @throws TemplateFileNotReadableException
     */
    final public function render()
    {
        $templatePath = realpath($this->template);

        if (!is_readable($templatePath) || is_dir($templatePath)) {
            throw new TemplateFileNotReadableException($templatePath);
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
    final public function addChildBlock($blockNameInLayout, Block $block)
    {
        $this->children[$blockNameInLayout] = $block;
    }

    /**
     * @param $blockName
     * @return string
     */
    final public function getChildOutput($blockName)
    {
        if (!array_key_exists($blockName, $this->children)) {
            return '';
        }

        return $this->children[$blockName]->render();
    }

    /**
     * @param string $string
     * @return string
     */
    public function __($string)
    {
        return $string;
    }
}
