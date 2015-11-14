<?php

namespace LizardsAndPumpkins\ContentDelivery\Catalog;

class FacetFieldRange
{
    /**
     * @var mixed
     */
    private $from;

    /**
     * @var mixed
     */
    private $to;

    /**
     * @param mixed $from
     * @param mixed $to
     */
    public function __construct($from, $to)
    {
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * @return mixed
     */
    public function from()
    {
        return $this->from;
    }

    /**
     * @return mixed
     */
    public function to()
    {
        return $this->to;
    }
}
