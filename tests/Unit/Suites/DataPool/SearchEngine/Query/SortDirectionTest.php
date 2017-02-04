<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\Query;

use LizardsAndPumpkins\ProductSearch\Exception\InvalidSortDirectionException;
use PHPUnit\Framework\TestCase;

/**
 * @covers \LizardsAndPumpkins\DataPool\SearchEngine\Query\SortDirection
 */
class SortDirectionTest extends TestCase
{
    public function testExceptionIsThrownIfInvalidSelectedSortingDirectionsIsSpecified()
    {
        $invalidSortDirection = 'foo';
        $this->expectException(InvalidSortDirectionException::class);
        SortDirection::create($invalidSortDirection);
    }

    public function testSortDirectionIsReturned()
    {
        $direction = 'asc';
        $result = SortDirection::create($direction);
        $this->assertSame($direction, (string) $result);
    }

    public function testExceptionIsThrownIfParameterIsNonString()
    {
        $this->expectException(\TypeError::class);
        $this->assertFalse(SortDirection::isValid(new \stdClass()));
    }

    /**
     * @dataProvider invalidSortDirectionProvider
     * @param mixed $invalidDirection
     */
    public function testFalseIsReturnedIfParameterIsNotValidSortDirection($invalidDirection)
    {
        $this->assertFalse(SortDirection::isValid($invalidDirection));
    }

    /**
     * @return array[]
     */
    public function invalidSortDirectionProvider() : array
    {
        return [
            ['foo'],
            ['aSc'],
            ['ASC'],
        ];
    }

    /**
     * @dataProvider validSortDirectionProvider
     * @param mixed $validDirection
     */
    public function testTrueIsReturnedIfParameterIsAValidSortDirection($validDirection)
    {
        $this->assertTrue(SortDirection::isValid($validDirection));
    }

    /**
     * @return array[]
     */
    public function validSortDirectionProvider() : array
    {
        return [
            ['asc'],
            ['desc'],
        ];
    }
}
