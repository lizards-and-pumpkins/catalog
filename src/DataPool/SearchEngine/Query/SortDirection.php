<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine\Query;

use LizardsAndPumpkins\ProductSearch\Exception\InvalidSortDirectionException;

class SortDirection
{
    const ASC = 'asc';
    const DESC = 'desc';

    /**
     * @var string
     */
    private $direction;

    private function __construct(string $direction)
    {
        $this->direction = $direction;
    }

    public static function create(string $direction) : SortDirection
    {
        if (!self::isValid($direction)) {
            throw new InvalidSortDirectionException(
                sprintf('Invalid selected sort order direction "%s" specified.', $direction)
            );
        }

        return new self($direction);
    }

    public function __toString() : string
    {
        return $this->direction;
    }

    public static function isValid(string $direction) : bool
    {
        return self::ASC === $direction || self::DESC === $direction;
    }
}
