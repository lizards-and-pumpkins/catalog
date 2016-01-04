<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\ContentDelivery\Catalog\Exception\InvalidNumberOfProductsPerPageException;
use LizardsAndPumpkins\ContentDelivery\Catalog\Exception\InvalidSelectedNumberOfProductsPerPageException;

class ProductsPerPage implements \JsonSerializable
{
    /**
     * @var int[]
     */
    private $numbersOfProductsPerPage;

    /**
     * @var int
     */
    private $selectedNumberOfProductsPerPage;

    /**
     * @param int[] $numbersOfProductsPerPage
     * @param int $selectedNumberOfProductsPerPage
     */
    private function __construct(array $numbersOfProductsPerPage, $selectedNumberOfProductsPerPage)
    {
        $this->numbersOfProductsPerPage = $numbersOfProductsPerPage;
        $this->selectedNumberOfProductsPerPage = $selectedNumberOfProductsPerPage;
    }

    /**
     * @param int[] $numbersOfProductsPerPage
     * @param int $selectedNumberOfProductsPerPage
     * @return ProductsPerPage
     */
    public static function create(array $numbersOfProductsPerPage, $selectedNumberOfProductsPerPage)
    {
        self::validateNumbersOfProductsPerPage($numbersOfProductsPerPage);
        self::validateSelectedNumberOfProductsPerPage($numbersOfProductsPerPage, $selectedNumberOfProductsPerPage);

        return new self($numbersOfProductsPerPage, $selectedNumberOfProductsPerPage);
    }

    /**
     * @return int[]
     */
    public function getNumbersOfProductsPerPage()
    {
        return $this->numbersOfProductsPerPage;
    }

    /**
     * @return int
     */
    public function getSelectedNumberOfProductsPerPage()
    {
        return $this->selectedNumberOfProductsPerPage;
    }

    /**
     * @param mixed[] $numbersOfProductsPerPage
     */
    private static function validateNumbersOfProductsPerPage(array $numbersOfProductsPerPage)
    {
        if (count($numbersOfProductsPerPage) === 0) {
            throw new InvalidNumberOfProductsPerPageException('No numbers of products per page specified.');
        }

        array_map(function ($numberOfProductsPerPage) {
            if (!is_int($numberOfProductsPerPage)) {
                throw new InvalidNumberOfProductsPerPageException(
                    sprintf('Number of products per page must be integer, got "%s".', gettype($numberOfProductsPerPage))
                );
            }
        }, $numbersOfProductsPerPage);
    }

    /**
     * @param mixed[] $numbersOfProductsPerPage
     * @param mixed $selectedNumberOfProductsPerPage
     */
    private static function validateSelectedNumberOfProductsPerPage(
        array $numbersOfProductsPerPage,
        $selectedNumberOfProductsPerPage
    ) {
        if (!is_int($selectedNumberOfProductsPerPage)) {
            throw new InvalidSelectedNumberOfProductsPerPageException(sprintf(
                'Selected number of products per page must be integer, got "%s".',
                gettype($selectedNumberOfProductsPerPage)
            ));
        }

        if (!in_array($selectedNumberOfProductsPerPage, $numbersOfProductsPerPage)) {
            throw new InvalidSelectedNumberOfProductsPerPageException(
                'Selected number of products per page is not from the list of available numbers of products per page.'
            );
        }
    }

    /**
     * @return array[]
     */
    public function jsonSerialize()
    {
        return array_map(function ($numberOfProductsPerPage) {
            return [
                'number' => $numberOfProductsPerPage,
                'selected' => $numberOfProductsPerPage === $this->selectedNumberOfProductsPerPage
            ];
        }, $this->numbersOfProductsPerPage);
    }
}
