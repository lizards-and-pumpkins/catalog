<?php

namespace LizardsAndPumpkins\Import\Tax;

use LizardsAndPumpkins\Import\Tax\Exception\InvalidTaxClassNameException;

/**
 * @covers \LizardsAndPumpkins\Import\Tax\ProductTaxClass
 */
class ProductTaxClassTest extends \PHPUnit_Framework_TestCase
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

    /**
     * @param mixed $nonString
     * @param string $expectedType
     * @dataProvider nonStringDataProvider
     */
    public function testItThrowsAnExceptionIfTheTaxClassNameIsNotAString($nonString, string $expectedType)
    {
        $this->expectException(InvalidTaxClassNameException::class);
        $this->expectExceptionMessage('The tax class name has to be a string, got "' . $expectedType . '"');
        ProductTaxClass::fromString($nonString);
    }

    public function testItReturnsTheGivenTaxClassInstanceIfATaxClassInstanceIsGiven()
    {
        $testClass = ProductTaxClass::fromString('test');
        $this->assertSame($testClass, ProductTaxClass::fromString($testClass));
    }

    /**
     * @return array[]
     */
    public function nonStringDataProvider() : array
    {
        return [
            [123, 'integer'],
            [[], 'array'],
            [$this, get_class($this)]
        ];
    }

    public function testTheNamedConstructorReturnsATaxClassInstance()
    {
        $this->assertInstanceOf(ProductTaxClass::class, ProductTaxClass::fromString('test'));
    }
}
