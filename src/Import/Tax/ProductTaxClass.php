<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Tax;

use LizardsAndPumpkins\Import\Tax\Exception\InvalidTaxClassNameException;

class ProductTaxClass
{
    /**
     * @var string
     */
    private $name;

    private function __construct(string $name)
    {
        $this->validateName($name);
        $this->name = $name;
    }

    /**
     * @param ProductTaxClass|string $name
     * @return ProductTaxClass
     */
    public static function fromString($name) : ProductTaxClass
    {
        return $name instanceof self ?
            $name :
            new self($name);
    }

    public function __toString() : string
    {
        return $this->name;
    }

    private function validateName(string $name): void
    {
        if ('' === trim($name)) {
            throw new InvalidTaxClassNameException('The tax class name can not be empty');
        }
    }
}
