<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionAnything;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\Exception\MalformedCriteriaQueryStringException;

/**
 * @covers \LizardsAndPumpkins\ProductSearch\ContentDelivery\DefaultCriteriaParser
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual
 */
class DefaultCriteriaParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DefaultCriteriaParser
     */
    private $parser;

    final protected function setUp()
    {
        $this->parser = new DefaultCriteriaParser();
    }

    public function testImplementsCriteriaParserInterface()
    {
        $this->assertInstanceOf(CriteriaParser::class, $this->parser);
    }

    /**
     * @dataProvider emptyStringProvider
     */
    public function testReturnsAnythingCriteriaForEmptyString(string $emptyString)
    {
        $this->assertInstanceOf(SearchCriterionAnything::class, $this->parser->parse($emptyString));
    }

    public function testCanParseSingleValue()
    {
        $expectedCriteria = new SearchCriterionEqual('foo', 'bar');
        $this->assertEquals($expectedCriteria, $this->parser->parse('foo:bar'));
    }

    public function testCanParseMultipleValuesFilterWithAndCriteria()
    {
        $expectedCriteria = CompositeSearchCriterion::createAnd(
            new SearchCriterionEqual('foo', 'bar'),
            new SearchCriterionEqual('foo', 'baz')
        );
        $this->assertEquals($expectedCriteria, $this->parser->parse('foo:{and:[bar,baz]}'));
    }

    public function testCanParseMultipleValuesFilterWithOrCriteria()
    {
        $expectedCriteria = CompositeSearchCriterion::createOr(
            new SearchCriterionEqual('foo', 'bar'),
            new SearchCriterionEqual('foo', 'baz')
        );
        $this->assertEquals($expectedCriteria, $this->parser->parse('foo:{or:[bar,baz]}'));
    }

    public function testCanParseMultipleCriteriaWithSingleValues()
    {
        $expectedCriteria = CompositeSearchCriterion::createOr(
            new SearchCriterionEqual('foo', 'bar'),
            new SearchCriterionEqual('baz', 'qux')
        );
        $this->assertEquals($expectedCriteria, $this->parser->parse('or:[foo:bar,baz:qux]'));
    }

    public function testCanParseMultipleCriteriaWithCompositeValues()
    {
        $criteriaString = 'or:[color:{and:[red,green]},color:{and:[yellow,blue]}]';
        $expectedCriteria = CompositeSearchCriterion::createOr(
            CompositeSearchCriterion::createAnd(
                new SearchCriterionEqual('color', 'red'),
                new SearchCriterionEqual('color', 'green')
            ),
            CompositeSearchCriterion::createAnd(
                new SearchCriterionEqual('color', 'yellow'),
                new SearchCriterionEqual('color', 'blue')
            )
        );
        $this->assertEquals($expectedCriteria, $this->parser->parse($criteriaString));
    }

    /**
     * @dataProvider malformedFiltersStringProvider
     */
    public function testExceptionIsThrownIfFiltersQueryStringIsMalformed(string $malformedFiltersString)
    {
        $this->expectException(MalformedCriteriaQueryStringException::class);
        $this->parser->parse($malformedFiltersString);
    }

    /**
     * @return array[]
     */
    public function malformedFiltersStringProvider(): array
    {
        return [
            ['foo'],
            ['foo:'],
            ['foo:{}'],
            ['foo:{bar}'],
            ['foo:{bar:baz}'],
            ['foo:{or:baz}'],
            ['foo:{and:[]}'],
            ['foo:{and:[bar]}'],
            ['foo:bar,baz'],
            ['foo:[]'],
            ['foo:['],
            ['foo:[}'],
            ['or:[foo:bar]'],
            ['or:[foo:bar,baz:{}]'],
            ['foo:[[]]'],
            ['foo: '],
            ['foo:[ ]'],
            ['foo: ,bar'],
            ['foo:[ ,bar]'],
        ];
    }

    /**
     * @return array[]
     */
    public function emptyStringProvider() : array
    {
        return [[''], [' '], ["\n"], ["\t"], ["\r"], ["\0"], ["\x0B"], [" \n\t"]];
    }
}
