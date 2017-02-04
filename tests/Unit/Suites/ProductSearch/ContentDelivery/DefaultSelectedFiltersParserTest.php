<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\ProductSearch\ContentDelivery\Exception\MalformedSelectedFiltersQueryStringException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductSearch\ContentDelivery\DefaultSelectedFiltersParser
 */
class DefaultSelectedFiltersParserTest extends TestCase
{
    /**
     * @var DefaultSelectedFiltersParser
     */
    private $parser;

    final protected function setUp()
    {
        $this->parser = new DefaultSelectedFiltersParser();
    }

    /**
     * @dataProvider emptyStringProvider
     */
    public function testReturnsAnEmptyArrayForAnEmptyString(string $emptyString)
    {
        $this->assertSame([], $this->parser->parse($emptyString));
    }

    public function testCanParseSingleValue()
    {
        $this->assertSame(['foo' => ['bar']], $this->parser->parse('foo:bar'));
    }

    public function testCanParseMultipleValuesFilter()
    {
        $this->assertSame(['foo' => ['bar', 'baz']], $this->parser->parse('foo:[bar,baz]'));
    }

    public function testCanParseMultipleFiltersWithSingleValues()
    {
        $this->assertSame(['foo' => ['bar'], 'baz' => ['qux']], $this->parser->parse('foo:bar,baz:qux'));
    }

    public function testCanParseMultipleFiltersWithMultipleValues()
    {
        $this->assertSame(['foo' => ['1', '2'], 'bar' => ['3', '4']], $this->parser->parse('foo:[1,2],bar:[3,4]'));
    }

    public function testCanParseMultipleFiltersWithMixedValues()
    {
        $this->assertSame(['foo' => ['1', '2'], 'bar' => ['baz']], $this->parser->parse('foo:[1,2],bar:baz'));
    }

    /**
     * @dataProvider malformedFiltersStringProvider
     */
    public function testExceptionIsThrownIfFiltersQueryStringIsMalformed(string $malformedFiltersString)
    {
        $this->expectException(MalformedSelectedFiltersQueryStringException::class);
        $this->parser->parse($malformedFiltersString);
    }

    /**
     * @return array[]
     */
    public function emptyStringProvider() : array
    {
        return [[''], [' '], ["\n"], ["\t"], ["\r"], ["\0"], ["\x0B"], [" \n\t"]];
    }

    /**
     * @return array[]
     */
    public function malformedFiltersStringProvider(): array
    {
        return [
            ['foo'],
            ['foo:'],
            ['foo:bar,baz'],
            ['foo:[]'],
            ['foo:[[]]'],
            ['foo: '],
            ['foo:[ ]'],
            ['foo: ,bar'],
            ['foo:[ ,bar]']
        ];
    }
}
