<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

use LizardsAndPumpkins\ContentDelivery\Catalog\Exception\InvalidSortOrderDirectionException;

class SortOrderDirection
{
    const ASC = 'asc';
    const DESC = 'desc';

    /**
     * @var string
     */
    private $direction;

    /**
     * @param string $direction
     */
    private function __construct($direction)
    {
        $this->direction = $direction;
    }

    /**
     * @param string $direction
     * @return SortOrderDirection
     */
    public static function create($direction)
    {
        if (self::ASC !== $direction && self::DESC !== $direction) {
            throw new InvalidSortOrderDirectionException(
                sprintf('Invalid selected sort order direction "%s" specified.', $direction)
            );
        }

        return new self($direction);
    }

    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }
}
