<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Import\Product\Exception\InvalidProductAttributeValueException;
use LizardsAndPumpkins\Import\Product\Exception\ProductAttributeDoesNotContainContextPartException;

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
    public static function fromArray(array $attribute) : ProductAttribute
    {
        return new self($attribute[self::CODE], $attribute[self::VALUE], $attribute[self::CONTEXT]);
    }

    /**
     * @param mixed $value
     * @param AttributeCode $code
     */
    private function validateValue($value, AttributeCode $code): void
    {
        if (!is_scalar($value)) {
            $type = typeof($value);
            $message = sprintf('The product attribute "%s" has to have a scalar value, got "%s"', $code, $type);
            throw new InvalidProductAttributeValueException($message);
        }
    }

    /**
     * @return string[]
     */
    public function getContextParts() : array
    {
        return array_keys($this->contextData);
    }

    public function hasSameContextPartsAs(ProductAttribute $attribute) : bool
    {
        $ownContextParts = $this->getContextParts();
        $foreignContextParts = $attribute->getContextParts();

        return
            !array_diff($ownContextParts, $foreignContextParts) &&
            !array_diff($foreignContextParts, $ownContextParts);
    }

    public function getCode() : AttributeCode
    {
        return $this->code;
    }

    /**
     * @param string|AttributeCode|ProductAttribute $attributeCode
     * @return bool
     */
    public function isCodeEqualTo($attributeCode) : bool
    {
        $codeToCompare = $attributeCode instanceof ProductAttribute ?
            $attributeCode->getCode() :
            $attributeCode;
        return $this->code->isEqualTo($codeToCompare);
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    public function getContextPartValue(string $contextPartCode) : string
    {
        $this->validateContextPartIsPresent($contextPartCode);
        return $this->contextData[$contextPartCode];
    }

    private function validateContextPartIsPresent(string $contextCode): void
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
    public function getContextDataSet() : array
    {
        return $this->contextData;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize() : array
    {
        return [
            self::CODE => $this->code->jsonSerialize(),
            self::CONTEXT => $this->contextData,
            self::VALUE => $this->value
        ];
    }

    public function isEqualTo(ProductAttribute $otherAttribute) : bool
    {
        return
            $this->isCodeEqualTo($otherAttribute) &&
            $this->isValueEqualTo($otherAttribute) &&
            $this->isContextDataSetEqualTo($otherAttribute);
    }

    private function isValueEqualTo(ProductAttribute $otherAttribute) : bool
    {
        return $this->value === $otherAttribute->getValue();
    }

    private function isContextDataSetEqualTo(ProductAttribute $otherAttribute) : bool
    {
        return $this->hasSameContextPartsAs($otherAttribute) && $this->hasSameContextPartValuesAs($otherAttribute);
    }

    private function hasSameContextPartValuesAs(ProductAttribute $otherAttribute) : bool
    {
        return array_reduce($otherAttribute->getContextParts(), function ($carry, $contextPart) use ($otherAttribute) {
            $myValue = $this->getContextPartValue($contextPart);
            $otherValue = $otherAttribute->getContextPartValue($contextPart);
            return $carry && $myValue === $otherValue;
        }, true);
    }
}
