<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionAnything;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\Exception\MalformedCriteriaQueryStringException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductSearch\ContentDelivery\DefaultCriteriaParser
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionEqual
 */
class DefaultCriteriaParserTest extends TestCase
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
        $this->assertInstanceOf(SearchCriterionAnything::class, $this->parser->createCriteriaFromString($emptyString));
    }

    public function testCanParseSingleValue()
    {
        $expectedCriteria = new SearchCriterionEqual('foo', 'bar');
        $this->assertEquals($expectedCriteria, $this->parser->createCriteriaFromString('foo:bar'));
    }

    public function testCanParseValueContainingWhitespace()
    {
        $expectedCriteria = new SearchCriterionEqual('foo', 'bar baz');
        $this->assertEquals($expectedCriteria, $this->parser->createCriteriaFromString('foo:bar baz'));
    }

    public function testCanParseMultipleValuesFilterWithAndCriteria()
    {
        $expectedCriteria = CompositeSearchCriterion::createAnd(
            new SearchCriterionEqual('foo', 'bar'),
            new SearchCriterionEqual('foo', 'baz')
        );
        $this->assertEquals($expectedCriteria, $this->parser->createCriteriaFromString('foo:{and:[bar,baz]}'));
    }

    public function testCanParseMultipleValuesFilterWithOrCriteria()
    {
        $expectedCriteria = CompositeSearchCriterion::createOr(
            new SearchCriterionEqual('foo', 'bar'),
            new SearchCriterionEqual('foo', 'baz')
        );
        $this->assertEquals($expectedCriteria, $this->parser->createCriteriaFromString('foo:{or:[bar,baz]}'));
    }

    public function testCanParseMultipleCriteriaWithSingleValues()
    {
        $expectedCriteria = CompositeSearchCriterion::createOr(
            new SearchCriterionEqual('foo', 'bar'),
            new SearchCriterionEqual('baz', 'qux')
        );
        $this->assertEquals($expectedCriteria, $this->parser->createCriteriaFromString('or:[foo:bar,baz:qux]'));
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
        $this->assertEquals($expectedCriteria, $this->parser->createCriteriaFromString($criteriaString));
    }

    /**
     * @dataProvider malformedFiltersStringProvider
     */
    public function testExceptionIsThrownIfFiltersQueryStringIsMalformed(string $malformedFiltersString)
    {
        $this->expectException(MalformedCriteriaQueryStringException::class);
        $this->parser->createCriteriaFromString($malformedFiltersString);
    }

    /**
     * @return array[]
     */
    public function malformedFiltersStringProvider(): array
    {
        return [
            'no-separator' => ['foo'],
            'no-value' => ['foo:'],
            'empty-value' => ['foo: '],
            'empty-condition' => ['foo:{}'],
            'incomplete-operation' => ['foo:{bar}'],
            'invalid-condition-operator' => ['foo:{bar:baz}'],
            'invalid-operation' => ['foo:{or:baz}'],
            'empty-operation' => ['foo:{and:[]}'],
            'too-few-operands' => ['foo:{and:[bar]}'],
            'completely-invalid-format' => ['foo:bar,baz'],
            'invalid-operator' => ['foo:[]'],
            'invalid-operator-no-operands' => ['foo:[ ]'],
            'no-closing-bracket' => ['foo:['],
            'non-matching-brackets' => ['foo:[}'],
            'too-few-conditions' => ['or:[foo:bar]'],
            'no-nested-operands' => ['or:[foo:bar,baz:{}]'],
            'invalid-nesting' => ['foo:[[]]'],
            'fuzz-test' => ['foo: ,bar'],
            'another-fuzz-test' => ['foo:[ ,bar]'],
            'leading-whitespace' => ['foo: bar'],
            'trailing-whitespace' => ['foo:bar '],
            'multiple-leading-whitespaces' => ['foo:  bar'],
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
