<?php

namespace Brera\Renderer;

class Block
{
    /**
     * @var string
     */
    private $template;

    /**
     * @var mixed
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
     * @param string $name
     * @param mixed $dataObject
     */
    final public function __construct(BlockRenderer $blockRenderer, $template, $name, $dataObject)
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
     * @return mixed
     */
    final protected function getDataObject()
    {
        return $this->dataObject;
    }

    /**
     * @return string
     */
    final public function getLayoutHandle()
    {
        return $this->blockRenderer->getLayoutHandle();
    }

    /**
     * @return string
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
     * @param string $childName
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
