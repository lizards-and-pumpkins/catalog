<?php

namespace Brera\Product;

use Brera\Attribute;
use Brera\Context\Context;
use Brera\Product\Exception\ProductAttributeDoesNotContainContextPartException;

class ProductAttribute implements Attribute
{
    const CODE = 'code';
    const VALUE = 'value';
    const CONTEXT_DATA = 'contextData';
    
    /**
     * @var string
     */
    private $code;

    /**
     * @var string[]
     */
    private $contextData;

    /**
     * @var string|ProductAttributeList
     */
    private $value;

    /**
     * @param string $code
     * @param string|ProductAttributeList $value
     * @param string[] $contextData
     */
    private function __construct($code, $value, array $contextData = [])
    {
        $this->code = $code;
        $this->contextData = $contextData;
        $this->value = $value;
    }

    /**
     * @param mixed[] $attribute
     * @return ProductAttribute
     */
    public static function fromArray(array $attribute)
    {
        return new self(
            $attribute[self::CODE],
            self::getValueRecursive($attribute[self::VALUE]),
            $attribute[self::CONTEXT_DATA]
        );
    }

    /**
     * @param string|mixed[] $attributeValue
     * @return string|ProductAttributeList
     */
    private static function getValueRecursive($attributeValue)
    {
        return is_array($attributeValue) ?
            ProductAttributeList::fromArray($attributeValue) :
            $attributeValue;
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
                return $score + $this->getScoreIfContextPartIsSetAndMatches($contextCode, $context);
            },
            0
        );
    }

    /**
     * @param string $contextCode
     * @param Context $context
     * @return int
     */
    private function getScoreIfContextPartIsSetAndMatches($contextCode, Context $context)
    {
        return array_key_exists($contextCode, $this->contextData) ?
            $this->getScoreIfContextPartMatches($contextCode, $context) :
            0;
    }

    /**
     * @param string $contextCode
     * @param Context $context
     * @return int
     */
    private function getScoreIfContextPartMatches($contextCode, Context $context)
    {
        return $context->getValue($contextCode) === $this->contextData[$contextCode] ?
            1 :
            0;
    }

    /**
     * @param string $contextPartCode
     * @return string
     */
    public function getContextPartValue($contextPartCode)
    {
        $this->validateContextPartIsPresent($contextPartCode);
        return $this->contextData[$contextPartCode];
    }

    /**
     * @param string $contextCode
     */
    private function validateContextPartIsPresent($contextCode)
    {
        if (!isset($this->contextData[$contextCode])) {
            throw new ProductAttributeDoesNotContainContextPartException(
                sprintf('The context part "%s" is not present on the attribute "%s"', $contextCode, $this->getCode())
            );
        }
    }
}
