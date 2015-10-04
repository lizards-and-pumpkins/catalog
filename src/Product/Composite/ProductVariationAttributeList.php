<?php


namespace LizardsAndPumpkins\Product\Composite;

use LizardsAndPumpkins\Product\AttributeCode;
use LizardsAndPumpkins\Product\Composite\Exception\ProductVariationAttributesEmptyException;
use LizardsAndPumpkins\Product\Composite\Exception\ProductVariationAttributesNotUniqueException;

class ProductVariationAttributeList implements \JsonSerializable, \IteratorAggregate
{
    /**
     * @var AttributeCode[]
     */
    private $attributeCodes;

    public function __construct(AttributeCode ...$attributeCodes)
    {
        $this->validateVariationAttributes(...$attributeCodes);
        $this->attributeCodes = $attributeCodes;
    }

    /**
     * @param string[] $attributeCodeStrings
     * @return ProductVariationAttributeList
     */
    public static function fromArray(array $attributeCodeStrings)
    {
        $attributeCodes = array_map(function ($code) {
            return AttributeCode::fromString($code);
        }, $attributeCodeStrings);
        return new self(...$attributeCodes);
    }

    private function validateVariationAttributes(AttributeCode ...$attributeCodes)
    {
        $this->validateAttributeCodeArrayIsNotEmpty(...$attributeCodes);
        $this->validateAttributeCodeArrayDoesNotContainDuplicates(...$attributeCodes);
    }

    private function validateAttributeCodeArrayIsNotEmpty(AttributeCode ...$attributeCodes)
    {
        if (empty($attributeCodes)) {
            throw new ProductVariationAttributesEmptyException('The product variation attribute list can not be empty');
        }
    }

    private function validateAttributeCodeArrayDoesNotContainDuplicates(AttributeCode ...$attributeCodes)
    {
        array_reduce($attributeCodes, function (array $stringCodes, AttributeCode $attributeCode) {
            $attributeCodeString = (string) $attributeCode;
            if (in_array($attributeCodeString, $stringCodes)) {
                throw $this->createVariationAttributeNotUniqueException($attributeCodeString);
            }
            return array_merge($stringCodes, [$attributeCodeString]);
        }, []);
    }

    /**
     * @param string $code
     * @return ProductVariationAttributesNotUniqueException
     */
    private function createVariationAttributeNotUniqueException($code)
    {
        $message = sprintf('The product variation attribute list contained the attribute "%s" more then once', $code);
        return new ProductVariationAttributesNotUniqueException($message);
    }

    /**
     * @return AttributeCode[]
     */
    public function jsonSerialize()
    {
        return $this->attributeCodes;
    }

    /**
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->attributeCodes);
    }

    /**
     * @return AttributeCode[]
     */
    public function getAttributes()
    {
        return $this->attributeCodes;
    }
}
