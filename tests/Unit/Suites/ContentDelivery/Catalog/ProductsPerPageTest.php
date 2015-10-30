<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\ContentDelivery\Catalog\Exception\InvalidNumberOfProductsPerPageException;
use LizardsAndPumpkins\ContentDelivery\Catalog\Exception\InvalidSelectedNumberOfProductsPerPageException;

/**
 * @covers LizardsAndPumpkins\ContentDelivery\Catalog\ProductsPerPage
 */
class ProductsPerPageTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider invalidNumbersOfProductsPerPageDataProvider
     * @param mixed[] $invalidNumbersOfProductsPerPage
     */
    public function testExceptionIsThrownIfNumbersOfProductsPerPageIsNotArrayOfIntegers(
        array $invalidNumbersOfProductsPerPage
    ) {
        $selectedNumberOfProductsPerPage = 1;
        $this->setExpectedException(InvalidNumberOfProductsPerPageException::class);
        ProductsPerPage::create($invalidNumbersOfProductsPerPage, $selectedNumberOfProductsPerPage);
    }

    public function invalidNumbersOfProductsPerPageDataProvider()
    {
        return [
            [[]],
            [['1']],
            [[1, '1']]
        ];
    }

    public function testExceptionIsThrownIfSelectedNumberOfProductsIsNotInteger()
    {
        $numbersOfProductsPrePage = [1, 2, 3];
        $invalidSelectedNumberOfProductsPerPage = '1';
        $this->setExpectedException(InvalidSelectedNumberOfProductsPerPageException::class);
        ProductsPerPage::create($numbersOfProductsPrePage, $invalidSelectedNumberOfProductsPerPage);
    }

    public function testExceptionIsThrownIfSelectedNUmberOfProductsPerPageIsAbsentInTheList()
    {
        $numbersOfProductsPrePage = [1, 2, 3];
        $selectedNumberOfProductsPerPage = 4;
        $this->setExpectedException(InvalidSelectedNumberOfProductsPerPageException::class);
        ProductsPerPage::create($numbersOfProductsPrePage, $selectedNumberOfProductsPerPage);
    }

    public function testNumbersOfProductsPerPageIsReturn()
    {
        $numbersOfProductsPrePage = [10, 20, 30];
        $selectedNumberOfProductsPerPage = 20;

        $productPerPage = ProductsPerPage::create($numbersOfProductsPrePage, $selectedNumberOfProductsPerPage);

        $this->assertSame($numbersOfProductsPrePage, $productPerPage->getNumbersOfProductsPerPage());
    }

    public function testSelectedNumberOfProductsPerPageIsReturned()
    {
        $numbersOfProductsPrePage = [10, 20, 30];
        $selectedNumberOfProductsPerPage = 20;

        $productPerPage = ProductsPerPage::create($numbersOfProductsPrePage, $selectedNumberOfProductsPerPage);

        $this->assertSame($selectedNumberOfProductsPerPage, $productPerPage->getSelectedNumberOfProductsPerPage());
    }
}
