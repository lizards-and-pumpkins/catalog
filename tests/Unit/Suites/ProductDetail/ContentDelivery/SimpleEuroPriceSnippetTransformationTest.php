<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ProductDetail\ContentDelivery;

use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\PageSnippets;
use LizardsAndPumpkins\Context\Context;
use LizardsAndPumpkins\Http\ContentDelivery\PageBuilder\SnippetTransformation\SnippetTransformation;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductDetail\ContentDelivery\SimpleEuroPriceSnippetTransformation
 */
class SimpleEuroPriceSnippetTransformationTest extends TestCase
{
    /**
     * @var SimpleEuroPriceSnippetTransformation
     */
    private $transformation;

    /**
     * @var Context|MockObject
     */
    private $stubContext;

    /**
     * @var PageSnippets|MockObject
     */
    private $stubPageSnippets;

    /**
     * @param string $expected
     * @param int|string|null $input
     */
    private function assertIsTransformedTo(string $expected, $input): void
    {
        $transformation = $this->transformation;
        $this->assertSame($expected, $transformation($input, $this->stubContext, $this->stubPageSnippets));
    }

    final protected function setUp(): void
    {
        $this->transformation = new SimpleEuroPriceSnippetTransformation();
        $this->stubContext = $this->createMock(Context::class);
        $this->stubPageSnippets = $this->createMock(PageSnippets::class);
    }

    public function testItIsCallable(): void
    {
        $this->assertInstanceOf(SnippetTransformation::class, $this->transformation);
        $this->assertTrue(is_callable($this->transformation), "Snippet transformations not callable");
    }

    public function testItIgnoresInputContainingNotOnlyNumbers(): void
    {
        $this->assertIsTransformedTo('12,3', '12,3');
        $this->assertIsTransformedTo('12.3', '12.3');
        $this->assertIsTransformedTo('12.30 €', '12.30 €');
    }

    public function testItReturnsNullInputAsAnEmptyString(): void
    {
        $this->assertIsTransformedTo('', null);
    }

    public function testItReturnsArrayInputAsAnEmptyString(): void
    {
        $this->assertIsTransformedTo('', []);
    }

    public function testItReturnsAnEmptyStringAsAnEmptyString(): void
    {
        $this->assertIsTransformedTo('', '');
    }

    /**
     * @dataProvider numbersOnlyInputDataProvider
     * @param string $expected
     * @param int|string $input
     */
    public function testItReturnsInputNumbersAsEuro(string $expected, $input): void
    {
        $this->assertIsTransformedTo($expected, $input);
    }

    /**
     * @return array[]
     */
    public function numbersOnlyInputDataProvider() : array
    {
        return [
            ['1,00 €', 100],
            ['1,00 €', '100'],
            ['0,01 €', 1],
            ['0,00 €', 0],
            ['-0,01 €', -1],
            ['-0,11 €', '-11'],
            ['12.345.678,99 €', 1234567899],
            ['12.345.678,99 €', '1234567899'],
        ];
    }
}
