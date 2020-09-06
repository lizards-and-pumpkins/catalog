<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductRelations\ContentDelivery;

use LizardsAndPumpkins\ProductRelations\Exception\InvalidProductRelationTypeCodeException;

class ProductRelationTypeCode
{
    /**
     * @var string
     */
    private $productRelationTypeCode;

    private function __construct(string $productRelationTypeCode)
    {
        $this->productRelationTypeCode = $productRelationTypeCode;
    }

    public static function fromString(string $relationTypeCode) : ProductRelationTypeCode
    {
        $trimmedRelationTypeCode = trim($relationTypeCode);
        self::validateStringFormat($trimmedRelationTypeCode);
        return new self($trimmedRelationTypeCode);
    }

    private static function validateStringFormat(string $relationTypeCode): void
    {
        if ('' === $relationTypeCode) {
            throw new InvalidProductRelationTypeCodeException('The product relation type code can not be empty');
        }
    }

    public function __toString() : string
    {
        return $this->productRelationTypeCode;
    }
}
