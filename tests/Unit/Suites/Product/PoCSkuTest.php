<?php

namespace Brera\Product;

/**
 * @covers \Brera\Product\SampleSku
 */
class SampleSkuTest extends \PHPUnit_Framework_TestCase
{
    public function testSkuInterfaceIsImplemented()
    {
        $sku = SampleSku::fromString('sku-string');
        $this->assertInstanceOf(Sku::class, $sku);
    }

    public function testSkuIsConvertedIntoString()
    {
        $skuString = 'sku-string';
        $sku = SampleSku::fromString($skuString);

        $this->assertSame($skuString, (string) $sku);
    }

    /**
     * @dataProvider invalidSkuProvider
     * @param mixed $invalidSku
     */
    public function testExceptionIsThrownIfSkuIsNotValid($invalidSku)
    {
        $this->setExpectedException(InvalidSkuException::class);
        SampleSku::fromString($invalidSku);
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
