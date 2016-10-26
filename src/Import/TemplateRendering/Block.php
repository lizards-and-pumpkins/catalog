<?php

declare(strict_types=1);

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
    final public function __construct(BlockRenderer $blockRenderer, string $template, string $name, $dataObject)
    {
        // TODO Decouple from template rendering logic
        $this->blockRenderer = $blockRenderer;
        $this->template = $template;
        $this->blockName = $name;
        $this->dataObject = $dataObject;
    }

    public function getBlockName() : string
    {
        return $this->blockName;
    }

    public function getBaseUrl() : BaseUrl
    {
        return $this->blockRenderer->getBaseUrl();
    }

    public function getWebsiteCode() : string
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

    final public function getLayoutHandle() : string
    {
        return $this->blockRenderer->getLayoutHandle();
    }

    final public function render() : string
    {
        $templatePath = realpath($this->template);

        if (false === $templatePath || !is_readable($templatePath) || is_dir($templatePath)) {
            throw new TemplateFileNotReadableException(sprintf('Template "%s" is not readable.', $this->template));
        }

        ob_start();

        include $templatePath;

        return ob_get_clean();
    }

    final public function getChildOutput(string $childName) : string
    {
        return $this->blockRenderer->getChildBlockOutput($this->blockName, $childName);
    }

    public function __(string $string) : string
    {
        return $this->blockRenderer->translate($string);
    }
}
