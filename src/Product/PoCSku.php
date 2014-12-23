<?php

namespace Brera\PoC\Product;

class PoCSku implements Sku
{
    /**
     * @var string
     */
    private $sku;

    /**
     * @param string $skuString
     */
    private function __construct($skuString)
    {
        $this->sku = $skuString;
    }

	/**
	 * @param string $skuString
	 * @throws InvalidSkuException
	 * @return PoCSku
	 */
	public static function fromString($skuString)
	{
		if (is_string($skuString) || (is_object($skuString) && method_exists($skuString, '__toString'))) {
			$skuString = trim($skuString);
		}

		if ((!is_string($skuString) && !is_int($skuString) && !is_float($skuString)) || empty($skuString)) {
			throw new InvalidSkuException();
		}

		return new self($skuString);
	}

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->sku;
    }

}
