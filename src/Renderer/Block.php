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
     * @var BlockRenderer
     */
    private $blockRenderer;

    /**
     * @var string
     */
    private $blockName;

    /**
     * @param BlockRenderer $blockRenderer
     * @param string $template
     * @param $name
     * @param ProjectionSourceData $dataObject
     */
    final public function __construct(BlockRenderer $blockRenderer, $template, $name, ProjectionSourceData $dataObject)
    {
        // TODO Decouple from template rendering logic
        $this->blockRenderer = $blockRenderer;
        $this->template = $template;
        $this->blockName = $name;
        $this->dataObject = $dataObject;
    }

    /**
     * @return string
     */
    public function getBlockName()
    {
        return $this->blockName;
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
            throw new TemplateFileNotReadableException(
                sprintf('The template path is not readable: "%s"', $this->template)
            );
        }

        ob_start();

        include $templatePath;

        return ob_get_clean();
    }

    /**
     * @param $childName
     * @return string
     */
    final public function getChildOutput($childName)
    {
        return $this->blockRenderer->getChildBlockOutput($this->blockName, $childName);
    }

    /**
     * @param string $string
     * @return string
     */
    // @codingStandardsIgnoreStart
    public function __($string)
    {
        // @codingStandardsIgnoreEnd
        return $string;
    }
}
