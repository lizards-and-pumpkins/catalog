<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product\Composite;

use LizardsAndPumpkins\Import\Product\AttributeCode;
use LizardsAndPumpkins\Import\Product\Composite\Exception\ProductVariationAttributesEmptyException;
use LizardsAndPumpkins\Import\Product\Composite\Exception\ProductVariationAttributesNotUniqueException;

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

    public static function fromStrings(string ...$attributeCodeStrings) : ProductVariationAttributeList
    {
        $attributeCodes = array_map(function ($code) {
            return AttributeCode::fromString($code);
        }, $attributeCodeStrings);

        return new self(...$attributeCodes);
    }

    private function validateVariationAttributes(AttributeCode ...$attributeCodes): void
    {
        $this->validateAttributeCodeArrayIsNotEmpty(...$attributeCodes);
        $this->validateAttributeCodeArrayDoesNotContainDuplicates(...$attributeCodes);
    }

    private function validateAttributeCodeArrayIsNotEmpty(AttributeCode ...$attributeCodes): void
    {
        if (count($attributeCodes) === 0) {
            throw new ProductVariationAttributesEmptyException('The product variation attribute list can not be empty');
        }
    }

    private function validateAttributeCodeArrayDoesNotContainDuplicates(AttributeCode ...$attributeCodes): void
    {
        array_reduce($attributeCodes, function (array $stringCodes, AttributeCode $attributeCode) {
            $attributeCodeString = (string) $attributeCode;
            if (in_array($attributeCodeString, $stringCodes)) {
                throw $this->createVariationAttributeNotUniqueException($attributeCodeString);
            }
            return array_merge($stringCodes, [$attributeCodeString]);
        }, []);
    }

    private function createVariationAttributeNotUniqueException(
        string $code
    ) : ProductVariationAttributesNotUniqueException {
        $message = sprintf('The product variation attribute list contained the attribute "%s" more then once', $code);
        return new ProductVariationAttributesNotUniqueException($message);
    }

    /**
     * @return AttributeCode[]
     */
    public function jsonSerialize() : array
    {
        return $this->attributeCodes;
    }

    public function getIterator() : \ArrayIterator
    {
        return new \ArrayIterator($this->attributeCodes);
    }

    /**
     * @return AttributeCode[]
     */
    public function getAttributes() : array
    {
        return $this->attributeCodes;
    }
}
