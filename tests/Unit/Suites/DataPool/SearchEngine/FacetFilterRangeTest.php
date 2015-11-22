<?php

namespace LizardsAndPumpkins\DataPool\SearchEngine;

use LizardsAndPumpkins\DataPool\SearchEngine\Exception\InvalidFacetFilterRangeBoundariesException;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\FacetFilterRange
 */
class FacetFilterRangeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider invalidRangeBoundariesDataProvider
     * @param mixed $rangeFrom
     * @param mixed $rangeTo
     * @param string $exceptionMessage
     */
    public function testExceptionIsThrownIfEitherOfRangeBoundariesIsNotScalar($rangeFrom, $rangeTo, $exceptionMessage)
    {
        $this->setExpectedException(InvalidFacetFilterRangeBoundariesException::class, $exceptionMessage);
        FacetFilterRange::create($rangeFrom, $rangeTo);
    }

    /**
     * @return array[]
     */
    public function invalidRangeBoundariesDataProvider()
    {
        $exceptionMessagePattern = 'Facet filter range boundary must be numeric, string or null, got "%s".';

        return [
            [1, [], sprintf($exceptionMessagePattern, 'array')],
            [1, false, sprintf($exceptionMessagePattern, 'boolean')],
            [new \stdClass(), 2, sprintf($exceptionMessagePattern, 'object')],
            [[], true, sprintf($exceptionMessagePattern, 'array')],
        ];
    }

    public function testExceptionIsThrownIfRangeBoundariesAreNotOfTheSameType()
    {
        $rangeFrom = 'a';
        $rangeTo = 1;
        $this->setExpectedException(
            InvalidFacetFilterRangeBoundariesException::class,
            'Facet filter rage boundaries must be the same type.'
        );
        FacetFilterRange::create($rangeFrom, $rangeTo);
    }

    /**
     * @dataProvider rangeBoundariesDataProvider
     * @param int|float|string|null $rangeFrom
     * @param int|float|string|null $rangeTo
     */
    public function testRangeBoundariesAreReturned($rangeFrom, $rangeTo)
    {
        $range = FacetFilterRange::create($rangeFrom, $rangeTo);
        $this->assertSame($rangeFrom, $range->from());
        $this->assertSame($rangeTo, $range->to());
    }

    /**
     * @return array[]
     */
    public function rangeBoundariesDataProvider()
    {
        return [
            [1, 1.5],
            ['a', 'z'],
            [null, 10],
            ['a', null],
        ];
    }
}
