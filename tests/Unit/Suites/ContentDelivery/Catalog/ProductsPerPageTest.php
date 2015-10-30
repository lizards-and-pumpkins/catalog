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
     * @var int[]
     */
    private $numbersOfProductsPerPage = [10, 20, 30];

    /**
     * @var int
     */
    private $selectedNumberOfProductsPerPage = 20;

    /**
     * @var ProductsPerPage
     */
    private $productsPerPage;

    protected function setUp()
    {
        $this->productsPerPage = ProductsPerPage::create(
            $this->numbersOfProductsPerPage,
            $this->selectedNumberOfProductsPerPage
        );
    }

    /**
     * @dataProvider invalidNumbersOfProductsPerPageDataProvider
     * @param mixed[] $invalidNumbersOfProductsPerPage
     */
    public function testExceptionIsThrownIfNumbersOfProductsPerPageIsNotArrayOfIntegers(
        array $invalidNumbersOfProductsPerPage
    ) {
        $this->setExpectedException(InvalidNumberOfProductsPerPageException::class);
        ProductsPerPage::create($invalidNumbersOfProductsPerPage, $this->selectedNumberOfProductsPerPage);
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
        $invalidSelectedNumberOfProductsPerPage = '1';
        $this->setExpectedException(InvalidSelectedNumberOfProductsPerPageException::class);
        ProductsPerPage::create($this->numbersOfProductsPerPage, $invalidSelectedNumberOfProductsPerPage);
    }

    public function testExceptionIsThrownIfSelectedNUmberOfProductsPerPageIsAbsentInTheList()
    {
        $selectedNumberOfProductsPerPage = 4;
        $this->setExpectedException(InvalidSelectedNumberOfProductsPerPageException::class);
        ProductsPerPage::create($this->numbersOfProductsPerPage, $selectedNumberOfProductsPerPage);
    }

    public function testNumbersOfProductsPerPageIsReturn()
    {
        $result = $this->productsPerPage->getNumbersOfProductsPerPage();
        $this->assertSame($this->numbersOfProductsPerPage, $result);
    }

    public function testSelectedNumberOfProductsPerPageIsReturned()
    {
        $result = $this->productsPerPage->getSelectedNumberOfProductsPerPage();
        $this->assertSame($this->selectedNumberOfProductsPerPage, $result);
    }

    public function testJsonSerializeInterfaceIsImplement()
    {
        $this->assertInstanceOf(\JsonSerializable::class, $this->productsPerPage);
    }

    public function testArrayRepresentationOfProductsPerPageIsReturned()
    {
        $expectedArray = array_map(function ($numberOfProductsPerPage) {
            return [
                'number' => $numberOfProductsPerPage,
                'selected' => $numberOfProductsPerPage === $this->selectedNumberOfProductsPerPage
            ];
        }, $this->numbersOfProductsPerPage);

        $this->assertSame($expectedArray, $this->productsPerPage->jsonSerialize());
    }
}