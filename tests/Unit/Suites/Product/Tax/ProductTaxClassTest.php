<?php


namespace LizardsAndPumpkins\Product\Tax;

use LizardsAndPumpkins\Product\Tax\Exception\InvalidTaxClassNameException;

/**
 * @covers \LizardsAndPumpkins\Product\Tax\ProductTaxClass
 */
class ProductTaxClassTest extends \PHPUnit_Framework_TestCase
{
    public function testItReturnsTheInjectedTaxClassValue()
    {
        $this->assertSame('test1', (string) ProductTaxClass::fromString('test1'));
        $this->assertSame('test2', (string) ProductTaxClass::fromString('test2'));
    }

    /**
     * @param string $emptyName
     * @dataProvider emptyTaxClassNameProvider
     */
    public function testItThrowsAnExceptionIfTheNameIsEmpty($emptyName)
    {
        $this->setExpectedException(
            InvalidTaxClassNameException::class,
            'The tax class name can not be empty'
        );
        ProductTaxClass::fromString($emptyName);
    }

    /**
     * @return array[]
     */
    public function emptyTaxClassNameProvider()
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
    public function testItThrowsAnExceptionIfTheTaxClassNameIsNotAString($nonString, $expectedType)
    {
        $this->setExpectedException(
            InvalidTaxClassNameException::class,
            'The tax class name has to be a string, got "' . $expectedType . '"'
        );
        ProductTaxClass::fromString($nonString);
    }

    /**
     * @return array[]
     */
    public function nonStringDataProvider()
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
