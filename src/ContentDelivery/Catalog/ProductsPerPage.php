<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\ContentDelivery\Catalog\Exception\InvalidNumberOfProductsPerPageException;
use LizardsAndPumpkins\ContentDelivery\Catalog\Exception\InvalidSelectedNumberOfProductsPerPageException;

class ProductsPerPage
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
    public function __construct(array $numbersOfProductsPerPage, $selectedNumberOfProductsPerPage)
    {
        $this->numbersOfProductsPerPage = $numbersOfProductsPerPage;
        $this->selectedNumberOfProductsPerPage = $selectedNumberOfProductsPerPage;
    }

    /**
     * @param mixed[] $numbersOfProducstPerPage
     * @param mixed $selectedNumberOfProductsPerPage
     * @return ProductsPerPage
     */
    public static function create(array $numbersOfProducstPerPage, $selectedNumberOfProductsPerPage)
    {
        self::validateNumbersOfProductsPerPage($numbersOfProducstPerPage);
        self::validateSelectedNumberOfProductsPerPage($numbersOfProducstPerPage, $selectedNumberOfProductsPerPage);

        return new self($numbersOfProducstPerPage, $selectedNumberOfProductsPerPage);
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
        if (empty($numbersOfProductsPerPage)) {
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
     * @param $selectedNumberOfProductsPerPage
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
}
