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
     * @var string|ProductAttributeListBuilder
     */
    private $value;

    /**
     * @param AttributeCode $code
     * @param string|ProductAttributeListBuilder $value
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
     * @return string|ProductAttributeListBuilder
     */
    private static function getValueRecursive($attributeValue)
    {
        return is_array($attributeValue) ?
            ProductAttributeListBuilder::fromArray($attributeValue) :
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
     * @return string|ProductAttributeListBuilder
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
            self::CONTEXT_DATA => $this->contextData,
            self::VALUE => $this->value
        ];
    }
}
