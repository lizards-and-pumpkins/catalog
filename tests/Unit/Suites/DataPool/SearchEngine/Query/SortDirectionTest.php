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
    public function testExceptionIsThrownIfInvalidSelectedSortingDirectionsIsSpecified(): void
    {
        $invalidSortDirection = 'foo';
        $this->expectException(InvalidSortDirectionException::class);
        SortDirection::create($invalidSortDirection);
    }

    public function testSortDirectionIsReturned(): void
    {
        $direction = 'asc';
        $result = SortDirection::create($direction);
        $this->assertSame($direction, (string) $result);
    }

    public function testExceptionIsThrownIfParameterIsNonString(): void
    {
        $this->expectException(\TypeError::class);
        $this->assertFalse(SortDirection::isValid(new \stdClass()));
    }

    /**
     * @dataProvider invalidSortDirectionProvider
     * @param mixed $invalidDirection
     */
    public function testFalseIsReturnedIfParameterIsNotValidSortDirection($invalidDirection): void
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
    public function testTrueIsReturnedIfParameterIsAValidSortDirection($validDirection): void
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
