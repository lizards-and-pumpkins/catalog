<?php


namespace LizardsAndPumpkins\Product\Tax;

use LizardsAndPumpkins\Product\Tax\Exception\InvalidTaxClassNameException;

class ProductTaxClassTest extends \PHPUnit_Framework_TestCase
{
    public function testItReturnsTheInjectedTaxClassValue()
    {
        $this->assertSame('test1', (new ProductTaxClass('test1'))->getName());
        $this->assertSame('test2', (new ProductTaxClass('test2'))->getName());
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
        new ProductTaxClass($emptyName);
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
        new ProductTaxClass($nonString);
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
}
