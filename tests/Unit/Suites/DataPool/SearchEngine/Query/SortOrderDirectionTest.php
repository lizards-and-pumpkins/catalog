<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\Query;

use LizardsAndPumpkins\ProductSearch\Exception\InvalidSortOrderDirectionException;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortOrderDirection
 */
class SortOrderDirectionTest extends \PHPUnit_Framework_TestCase
{
    public function testExceptionIsThrownIfInvalidSelectedSortingDirectionsIsSpecified()
    {
        $invalidSortOrderDirection = 'foo';
        $this->expectException(InvalidSortOrderDirectionException::class);
        SortOrderDirection::create($invalidSortOrderDirection);
    }

    public function testSortOrderDirectionIsReturned()
    {
        $direction = 'asc';
        $result = SortOrderDirection::create($direction);
        $this->assertSame($direction, (string) $result);
    }

    /**
     * @dataProvider invalidSortOrderDirectionProvider
     * @param mixed $invalidDirection
     */
    public function testFalseIsReturnedIfParameterIsNotValidSortOrderDirection($invalidDirection)
    {
        $this->assertFalse(SortOrderDirection::isValid($invalidDirection));
    }

    /**
     * @return array[]
     */
    public function invalidSortOrderDirectionProvider() : array
    {
        return [
            ['foo'],
            ['aSc'],
            ['ASC'],
        ];
    }

    /**
     * @dataProvider validSortOrderDirectionProvider
     * @param mixed $validDirection
     */
    public function testTrueIsReturnedIfParameterIsAValidSortOrderDirection($validDirection)
    {
        $this->assertTrue(SortOrderDirection::isValid($validDirection));
    }

    /**
     * @return array[]
     */
    public function validSortOrderDirectionProvider() : array
    {
        return [
            ['asc'],
            ['desc'],
        ];
    }
}
