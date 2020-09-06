<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\TemplateRendering;

use LizardsAndPumpkins\Import\TemplateRendering\Exception\RootElementOfLayoutMustBeAnArrayException;

class Layout
{
    /**
     * @var string[]
     */
    private $nodeAttributes;

    /**
     * @var string|Layout[]
     */
    private $nodeChildren;

    /**
     * @param string[] $nodeAttributes
     * @param string|mixed[] $nodeChildren
     */
    private function __construct(array $nodeAttributes, $nodeChildren)
    {
        $this->nodeAttributes = $nodeAttributes;
        $this->nodeChildren = $nodeChildren;
    }

    /**
     * @param mixed[] $layoutArray
     * @return Layout
     */
    public static function fromArray(array $layoutArray) : Layout
    {
        $rootElement = self::getRootElement($layoutArray);
        $layoutArray = array_merge(['attributes' => [], 'value' => ''], $rootElement);

        return new self($layoutArray['attributes'], self::getValue($layoutArray['value']));
    }

    /**
     * @return string[]
     */
    public function getAttributes() : array
    {
        return $this->nodeAttributes;
    }

    public function getAttribute(string $attributeCode): ?string
    {
        if (!array_key_exists($attributeCode, $this->nodeAttributes)) {
            return null;
        }

        return $this->nodeAttributes[$attributeCode];
    }

    /**
     * @return string|Layout[]
     */
    public function getNodeChildren()
    {
        return $this->nodeChildren;
    }

    /**
     * @return bool
     */
    public function hasChildren() : bool
    {
        return is_array($this->nodeChildren);
    }

    /**
     * @param mixed[] $layout
     * @return array[]
     */
    private static function getRootElement(array $layout) : array
    {
        $rootElement = array_shift($layout);

        if (!is_array($rootElement)) {
            throw new RootElementOfLayoutMustBeAnArrayException();
        }

        return $rootElement;
    }

    /**
     * @param string|array[] $layout
     * @return string|Layout[]
     */
    private static function getValue($layout)
    {
        if (!self::hasChildNodes($layout)) {
            return $layout;
        }

        $values = [];

        foreach ($layout as $element) {
            $element = array_merge(['attributes' => [], 'value' => ''], $element);
            $values[] = new self($element['attributes'], self::getValue($element['value']));
        }

        return $values;
    }

    /**
     * @param string|array[] $layout
     * @return bool
     */
    private static function hasChildNodes($layout) : bool
    {
        return is_array($layout);
    }
}
