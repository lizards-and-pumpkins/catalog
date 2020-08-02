<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\ContentDelivery;

use LizardsAndPumpkins\ProductListing\Exception\InvalidNumberOfProductsPerPageException;
use LizardsAndPumpkins\ProductListing\Exception\InvalidSelectedNumberOfProductsPerPageException;

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
    private function __construct(array $numbersOfProductsPerPage, int $selectedNumberOfProductsPerPage)
    {
        $this->numbersOfProductsPerPage = $numbersOfProductsPerPage;
        $this->selectedNumberOfProductsPerPage = $selectedNumberOfProductsPerPage;
    }

    /**
     * @param int[] $numbersOfProductsPerPage
     * @param int $selectedNumberOfProductsPerPage
     * @return ProductsPerPage
     */
    public static function create(
        array $numbersOfProductsPerPage,
        int $selectedNumberOfProductsPerPage
    ) : ProductsPerPage {
        self::validateNumbersOfProductsPerPage($numbersOfProductsPerPage);
        self::validateSelectedNumberOfProductsPerPage($numbersOfProductsPerPage, $selectedNumberOfProductsPerPage);

        return new self($numbersOfProductsPerPage, $selectedNumberOfProductsPerPage);
    }

    /**
     * @return int[]
     */
    public function getNumbersOfProductsPerPage() : array
    {
        return $this->numbersOfProductsPerPage;
    }

    public function getSelectedNumberOfProductsPerPage() : int
    {
        return $this->selectedNumberOfProductsPerPage;
    }

    /**
     * @param mixed[] $numbersOfProductsPerPage
     */
    private static function validateNumbersOfProductsPerPage(array $numbersOfProductsPerPage): void
    {
        if (count($numbersOfProductsPerPage) === 0) {
            throw new InvalidNumberOfProductsPerPageException('No numbers of products per page specified.');
        }

        every($numbersOfProductsPerPage, function ($numberOfProductsPerPage) {
            if (!is_int($numberOfProductsPerPage)) {
                throw new InvalidNumberOfProductsPerPageException(
                    sprintf('Number of products per page must be integer, got "%s".', gettype($numberOfProductsPerPage))
                );
            }
        });
    }

    /**
     * @param mixed[] $numbersOfProductsPerPage
     * @param int $selectedNumberOfProductsPerPage
     */
    private static function validateSelectedNumberOfProductsPerPage(
        array $numbersOfProductsPerPage,
        int $selectedNumberOfProductsPerPage
    ) {
        if (!in_array($selectedNumberOfProductsPerPage, $numbersOfProductsPerPage)) {
            throw new InvalidSelectedNumberOfProductsPerPageException(
                'Selected number of products per page is not from the list of available numbers of products per page.'
            );
        }
    }

    /**
     * @return array[]
     */
    public function jsonSerialize() : array
    {
        return array_map(function ($numberOfProductsPerPage) {
            return [
                'number' => $numberOfProductsPerPage,
                'selected' => $numberOfProductsPerPage === $this->selectedNumberOfProductsPerPage
            ];
        }, $this->numbersOfProductsPerPage);
    }
}
