<?php

namespace Brera\Product;

use Brera\Attribute;
use Brera\Context\Context;

class ProductAttribute implements Attribute
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var array
     */
    private $contextData;

    /**
     * @var string|ProductAttributeList
     */
    private $value;

    /**
     * @param string $code
     * @param string|ProductAttributeList $value
     * @param mixed[] $contextData
     */
    private function __construct($code, $value, array $contextData = [])
    {
        $this->code = $code;
        $this->contextData = $contextData;
        $this->value = $value;
    }

    /**
     * @param mixed[] $node
     * @return ProductAttribute
     */
    public static function fromArray(array $node)
    {
        return new self($node['nodeName'], self::getValueRecursive($node['value']), $node['attributes']);
    }

    /**
     * @return string[]
     */
    public function getContextParts()
    {
        return array_keys($this->contextData);
    }

    /**
     * @param ProductAttribute $attribute
     * @return bool
     */
    public function hasSameContextPartsAs(ProductAttribute $attribute)
    {
        $ownContextParts = $this->getContextParts();
        $foreignContextParts = $attribute->getContextParts();

        return !array_diff($ownContextParts, $foreignContextParts) &&
               !array_diff($foreignContextParts, $ownContextParts);
    }

    /**
     * @param ProductAttribute $attribute
     * @return bool
     */
    public function hasSameCodeAs(ProductAttribute $attribute)
    {
        return $this->code === $attribute->getCode();
    }

    /**
     * @param array|string $nodeValue
     * @return string|ProductAttributeList
     */
    private static function getValueRecursive($nodeValue)
    {
        if (!is_array($nodeValue)) {
            return $nodeValue;
        }

        $list = new ProductAttributeList();

        foreach ($nodeValue as $node) {
            $list->add(new self($node['nodeName'], self::getValueRecursive($node['value']), $node['attributes']));
        }

        return $list;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $codeExpectation
     * @return bool
     */
    public function isCodeEqualsTo($codeExpectation)
    {
        return $codeExpectation == $this->code;
    }

    /**
     * @return string|ProductAttributeList
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param Context $context
     * @return int
     */
    public function getMatchScoreForContext(Context $context)
    {
        return array_reduce(
            $context->getSupportedCodes(),
            function ($score, $contextCode) use ($context) {
                return $score + $this->getScoreIfContextIsSetAndMatches(
                    $contextCode,
                    $context
                );
            },
            0
        );
    }

    /**
     * @param string $contextCode
     * @param Context $context
     * @return int
     */
    private function getScoreIfContextIsSetAndMatches($contextCode, Context $context)
    {
        return array_key_exists($contextCode, $this->contextData) ?
            $this->getScoreIfContextMatches($contextCode, $context) :
            0;
    }

    /**
     * @param string $contextCode
     * @param Context $context
     * @return int
     */
    private function getScoreIfContextMatches($contextCode, Context $context)
    {
        return $context->getValue($contextCode) === $this->contextData[$contextCode] ?
            1 :
            0;
    }
}
