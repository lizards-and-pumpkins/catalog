<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ProductSearch\ContentDelivery;

use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion;
use LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionFullText;
use LizardsAndPumpkins\ProductSearch\ContentDelivery\Exception\EmptyQueryStringException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\ProductSearch\ContentDelivery\DefaultFullTextCriteriaBuilder
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\CompositeSearchCriterion
 * @uses   \LizardsAndPumpkins\DataPool\SearchEngine\SearchCriteria\SearchCriterionFullText
 */
class DefaultFullTextCriteriaBuilderTest extends TestCase
{
    public function testImplementsFullTextCriteriaBuilderInterface()
    {
        $fullTextSearchTermCombinationOperator = CompositeSearchCriterion::OR_CONDITION;
        $builder = new DefaultFullTextCriteriaBuilder($fullTextSearchTermCombinationOperator);

        $this->assertInstanceOf(FullTextCriteriaBuilder::class, $builder);
    }

    public function testCreatesASimpleCriteriaIfQueryStringContainsOfASingleWOrd()
    {
        $fullTextSearchTermCombinationOperator = CompositeSearchCriterion::OR_CONDITION;
        $builder = new DefaultFullTextCriteriaBuilder($fullTextSearchTermCombinationOperator);

        $expectedCriteria = new SearchCriterionFullText('foo');

        $this->assertEquals($expectedCriteria, $builder->createFromString('foo'));
    }

    public function testThrowsAnErrorDuringAttemptToCreateACriteriaFromNonString()
    {
        $this->expectException(\TypeError::class);

        $fullTextSearchTermCombinationOperator = CompositeSearchCriterion::OR_CONDITION;
        $builder = new DefaultFullTextCriteriaBuilder($fullTextSearchTermCombinationOperator);

        $builder->createFromString(123);
    }

    /**
     * @dataProvider emptyStringProvider
     */
    public function testThrowsAnExceptionDuringAttemptToCreateACriteriaFromEmptyString(string $emptyString)
    {
        $this->expectException(EmptyQueryStringException::class);

        $fullTextSearchTermCombinationOperator = CompositeSearchCriterion::OR_CONDITION;
        $builder = new DefaultFullTextCriteriaBuilder($fullTextSearchTermCombinationOperator);

        $builder->createFromString($emptyString);
    }
    
    /**
     * @dataProvider fullTextSearchTermCombinationOperatorProvider
     */
    public function testCreatesACombinedCriteriaIfQueryStringContainsOfMultipleWords(string $fullTextSearchOperator)
    {
        $builder = new DefaultFullTextCriteriaBuilder($fullTextSearchOperator);

        $expectedCriteria = CompositeSearchCriterion::create(
            $fullTextSearchOperator,
            new SearchCriterionFullText('foo'),
            new SearchCriterionFullText('bar')
        );

        $this->assertEquals($expectedCriteria, $builder->createFromString('foo bar'));
    }

    public function emptyStringProvider() : array
    {
        return [[''], [' '], ["\n"], ["\t"], ["\r"], ["\0"], ["\x0B"], [" \n\t"]];
    }

    public function fullTextSearchTermCombinationOperatorProvider(): array
    {
        return [
            [CompositeSearchCriterion::OR_CONDITION],
            [CompositeSearchCriterion::AND_CONDITION],
        ];
    }
}
