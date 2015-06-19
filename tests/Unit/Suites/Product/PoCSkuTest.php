<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\PoCSku
 */
class PoCSkuTest extends \PHPUnit_Framework_TestCase
{
    public function testSkuInterfaceIsImplemented()
    {
        $sku = PoCSku::fromString('sku-string');
        $this->assertInstanceOf(Sku::class, $sku);
    }

    public function testSkuIsConvertedIntoString()
    {
        $skuString = 'sku-string';
        $sku = PoCSku::fromString($skuString);

        $this->assertSame($skuString, (string) $sku);
    }

    /**
     * @dataProvider invalidSkuProvider
     * @param mixed $invalidSku
     */
    public function testExceptionIsThrownIfSkuIsNotValid($invalidSku)
    {
        $this->setExpectedException(InvalidSkuException::class);
        PoCSku::fromString($invalidSku);
    }

    /**
     * @return mixed[]
     */
    public function invalidSkuProvider()
    {
        return [
        [null],
        [[]],
        [new \stdClass()],
        [true],
        [false],
        [''],
        ['  '],
        ["\n"],
        ["\t"]
        ];
    }
}
