<?php


namespace LizardsAndPumpkins\Product\Tax;

use LizardsAndPumpkins\Product\Tax\Exception\InvalidTaxClassNameException;

class ProductTaxClass
{
    /**
     * @var string
     */
    private $name;

    /**
     * @param string $name
     */
    private function __construct($name)
    {
        $this->validateName($name);
        $this->name = $name;
    }

    /**
     * @param string $name
     * @return ProductTaxClass
     */
    public static function fromString($name)
    {
        return new self($name);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    private function validateName($name)
    {
        if (! is_string($name)) {
            $message = sprintf('The tax class name has to be a string, got "%s"', $this->getVariableType($name));
            throw new InvalidTaxClassNameException($message);
        }
        if ('' === trim($name)) {
            throw new InvalidTaxClassNameException('The tax class name can not be empty');
        }
    }

    /**
     * @param mixed $variable
     * @return string
     */
    private function getVariableType($variable)
    {
        return is_object($variable) ?
            get_class($variable) :
            gettype($variable);
    }
}
