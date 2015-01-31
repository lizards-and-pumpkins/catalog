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

    public final function __construct($template, ProjectionSourceData $dataObject)
    {
        $this->template = $template;
        $this->dataObject = $dataObject;
    }

    /**
     * @return ProjectionSourceData
     */
    protected final function getDataObject()
    {
        return $this->dataObject;
    }

    /**
     * @return string
     * @throws TemplateFileNotReadableException
     */
    public final function render()
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
    public final function addChildBlock($blockNameInLayout, Block $block)
    {
        $this->children[$blockNameInLayout] = $block;
    }

    /**
     * @param $blockName
     * @return string
     */
    public final function getChildOutput($blockName)
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
