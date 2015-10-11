<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Product\Exception\InvalidProductAttributeValueException;
use LizardsAndPumpkins\Product\Exception\ProductAttributeDoesNotContainContextPartException;

class ProductAttribute implements \JsonSerializable
{
    const CODE = 'code';
    const VALUE = 'value';
    const CONTEXT = 'contextData';

    /**
     * @var AttributeCode
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
     * @param AttributeCode|string $code
     * @param string|ProductAttributeList $value
     * @param string[] $contextData
     */
    public function __construct($code, $value, array $contextData)
    {
        $attributeCode = AttributeCode::fromString($code);
        $this->validateValue($value, $attributeCode);
        $this->code = $attributeCode;
        $this->contextData = $contextData;
        $this->value = $value;
    }

    /**
     * @param mixed[] $attribute
     * @return ProductAttribute
     */
    public static function fromArray(array $attribute)
    {
        return new self($attribute[self::CODE], $attribute[self::VALUE], $attribute[self::CONTEXT]);
    }

    /**
     * @param string $value
     * @param AttributeCode $code
     */
    private function validateValue($value, AttributeCode $code)
    {
        if (!is_scalar($value)) {
            $type = $this->getType($value);
            $message = sprintf('The product attribute "%s" has to have a scalar value, got "%s"', $code, $type);
            throw new InvalidProductAttributeValueException($message);
        }
    }

    /**
     * @param mixed $variable
     * @return string
     */
    private function getType($variable)
    {
        return is_object($variable) ?
            get_class($variable) :
            gettype($variable);
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

        return
            !array_diff($ownContextParts, $foreignContextParts) &&
            !array_diff($foreignContextParts, $ownContextParts);
    }

    /**
     * @return AttributeCode
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string|AttributeCode|ProductAttribute $attributeCode
     * @return bool
     */
    public function isCodeEqualTo($attributeCode)
    {
        $codeToCompare = $attributeCode instanceof ProductAttribute ?
            $attributeCode->getCode() :
            $attributeCode;
        return $this->code->isEqualTo($codeToCompare);
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
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

    /**
     * @return string[]
     */
    public function getContextDataSet()
    {
        return $this->contextData;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize()
    {
        return [
            self::CODE => $this->code,
            self::CONTEXT => $this->contextData,
            self::VALUE => $this->value
        ];
    }

    /**
     * @param ProductAttribute $otherAttribute
     * @return bool
     */
    public function isEqualTo(ProductAttribute $otherAttribute)
    {
        return
            $this->isCodeEqualTo($otherAttribute) &&
            $this->isValueEqualTo($otherAttribute) &&
            $this->isContextDataSetEqualTo($otherAttribute);
    }

    /**
     * @param ProductAttribute $otherAttribute
     * @return bool
     */
    private function isValueEqualTo(ProductAttribute $otherAttribute)
    {
        return $this->value === $otherAttribute->getValue();
    }

    /**
     * @param ProductAttribute $otherAttribute
     * @return bool
     */
    private function isContextDataSetEqualTo(ProductAttribute $otherAttribute)
    {
        return $this->hasSameContextPartsAs($otherAttribute) && $this->hasSameContextPartValuesAs($otherAttribute);
    }

    /**
     * @param ProductAttribute $otherAttribute
     * @return bool
     */
    private function hasSameContextPartValuesAs(ProductAttribute $otherAttribute)
    {
        return array_reduce($otherAttribute->getContextParts(), function ($carry, $contextPart) use ($otherAttribute) {
            $myValue = $this->getContextPartValue($contextPart);
            $otherValue = $otherAttribute->getContextPartValue($contextPart);
            return $carry && $myValue === $otherValue;
        }, true);
    }
}
