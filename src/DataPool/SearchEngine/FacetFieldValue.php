<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\SearchEngine;

class FacetFieldValue implements \JsonSerializable
{
    /**
     * @var string
     */
    private $value;

    /**
     * @var int
     */
    private $count;

    public function __construct(string $value, int $count)
    {
        $this->value = $value;
        $this->count = $count;
    }

    /**
     * @return mixed[]
     */
    public function jsonSerialize() : array
    {
        return [
            'value' => $this->value,
            'count' => $this->count
        ];
    }
}
