<?php

namespace LizardsAndPumpkins\Import\TemplateRendering;

use LizardsAndPumpkins\Context\BaseUrl\BaseUrl;
use LizardsAndPumpkins\Import\RootTemplate\Import\Exception\TemplateFileNotReadableException;

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
     * @return BaseUrl
     */
    public function getBaseUrl()
    {
        return $this->blockRenderer->getBaseUrl();
    }

    /**
     * @return string
     */
    public function getWebsiteCode()
    {
        return $this->blockRenderer->getWebsiteCode();
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
            throw new TemplateFileNotReadableException(sprintf('Template "%s" is not readable.', $this->template));
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
    public function __($string)
    {
        return $this->blockRenderer->translate($string);
    }
}
