<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductListing\ContentDelivery;

use LizardsAndPumpkins\ProductListing\Exception\InvalidNumberOfProductsPerPageException;
use LizardsAndPumpkins\ProductListing\Exception\InvalidSelectedNumberOfProductsPerPageException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductListing\ContentDelivery\ProductsPerPage
 */
class ProductsPerPageTest extends TestCase
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

    final protected function setUp(): void
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
        $this->expectException(InvalidNumberOfProductsPerPageException::class);
        ProductsPerPage::create($invalidNumbersOfProductsPerPage, $this->selectedNumberOfProductsPerPage);
    }

    /**
     * @return array[]
     */
    public function invalidNumbersOfProductsPerPageDataProvider() : array
    {
        return [
            [[]],
            [['1']],
            [[1, '1']]
        ];
    }

    public function testExceptionIsThrownIfSelectedNumberOfProductsIsNotInteger(): void
    {
        $this->expectException(\TypeError::class);
        $invalidSelectedNumberOfProductsPerPage = '1';
        ProductsPerPage::create($this->numbersOfProductsPerPage, $invalidSelectedNumberOfProductsPerPage);
    }

    public function testExceptionIsThrownIfSelectedNumberOfProductsPerPageIsAbsentInTheList(): void
    {
        $selectedNumberOfProductsPerPage = 4;
        $this->expectException(InvalidSelectedNumberOfProductsPerPageException::class);
        ProductsPerPage::create($this->numbersOfProductsPerPage, $selectedNumberOfProductsPerPage);
    }

    public function testNumbersOfProductsPerPageIsReturn(): void
    {
        $result = $this->productsPerPage->getNumbersOfProductsPerPage();
        $this->assertSame($this->numbersOfProductsPerPage, $result);
    }

    public function testSelectedNumberOfProductsPerPageIsReturned(): void
    {
        $result = $this->productsPerPage->getSelectedNumberOfProductsPerPage();
        $this->assertSame($this->selectedNumberOfProductsPerPage, $result);
    }

    public function testJsonSerializeInterfaceIsImplement(): void
    {
        $this->assertInstanceOf(\JsonSerializable::class, $this->productsPerPage);
    }

    public function testArrayRepresentationOfProductsPerPageIsReturned(): void
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
