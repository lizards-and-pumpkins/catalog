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
    private $context;

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
        $this->context = $contextData;
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
        return array_key_exists($contextCode, $this->context) ?
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
        return $context->getValue($contextCode) === $this->context[$contextCode] ?
            1 :
            0;
    }
}
