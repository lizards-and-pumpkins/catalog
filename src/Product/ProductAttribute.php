<?php

namespace LizardsAndPumpkins\Product;

use LizardsAndPumpkins\Attribute;
use LizardsAndPumpkins\Product\Exception\ProductAttributeDoesNotContainContextPartException;

class ProductAttribute implements \JsonSerializable
{
    const CODE = 'code';
    const VALUE = 'value';
    const CONTEXT_DATA = 'contextData';
    
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
     * @param AttributeCode $code
     * @param string|ProductAttributeList $value
     * @param string[] $contextData
     */
    private function __construct(AttributeCode $code, $value, array $contextData)
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
            AttributeCode::fromString($attribute[self::CODE]),
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
            (string) $attributeValue;
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
        return strval($this->code) === strval($attribute->getCode());
    }

    /**
     * @return AttributeCode
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string|AttributeCode $codeToCompare
     * @return bool
     */
    public function isCodeEqualTo($codeToCompare)
    {
        return $this->code->isEqualTo($codeToCompare);
    }

    /**
     * @return string|ProductAttributeList
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
            self::CODE => $this->code->jsonSerialize(),
            self::CONTEXT_DATA => $this->contextData,
            self::VALUE => $this->getSerializableValue($this->value)
        ];
    }

    /**
     * @param string|ProductAttributeList $value
     * @return string|array[]
     */
    private function getSerializableValue($value)
    {
        return is_object($value) ?
            $value->jsonSerialize() :
            (string) $value;
    }
}
