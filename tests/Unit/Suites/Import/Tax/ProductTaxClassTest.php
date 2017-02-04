<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Tax;

use LizardsAndPumpkins\Import\Tax\Exception\InvalidTaxClassNameException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\Import\Tax\ProductTaxClass
 */
class ProductTaxClassTest extends TestCase
{
    public function testItReturnsTheInjectedTaxClassValue()
    {
        $this->assertSame('test1', (string) ProductTaxClass::fromString('test1'));
        $this->assertSame('test2', (string) ProductTaxClass::fromString('test2'));
    }

    /**
     * @dataProvider emptyTaxClassNameProvider
     */
    public function testItThrowsAnExceptionIfTheNameIsEmpty(string $emptyName)
    {
        $this->expectException(InvalidTaxClassNameException::class);
        $this->expectExceptionMessage('The tax class name can not be empty');
        ProductTaxClass::fromString($emptyName);
    }

    /**
     * @return array[]
     */
    public function emptyTaxClassNameProvider() : array
    {
        return [
            'zero length string' => [''],
            'string containing only spaces' => ['  ']
        ];
    }

    public function testItThrowsAnExceptionIfTheTaxClassNameIsNotAString()
    {
        $this->expectException(\TypeError::class);
        ProductTaxClass::fromString([]);
    }

    public function testItReturnsTheGivenTaxClassInstanceIfATaxClassInstanceIsGiven()
    {
        $testClass = ProductTaxClass::fromString('test');
        $this->assertSame($testClass, ProductTaxClass::fromString($testClass));
    }

    public function testTheNamedConstructorReturnsATaxClassInstance()
    {
        $this->assertInstanceOf(ProductTaxClass::class, ProductTaxClass::fromString('test'));
    }
}
