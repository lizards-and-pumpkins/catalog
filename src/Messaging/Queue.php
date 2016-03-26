<?php

namespace LizardsAndPumpkins\Messaging;

interface Queue extends \Countable
{
    /**
     * @return int
     */
    public function count();

    /**
     * @return bool
     */
    public function isReadyForNext();

    /**
     * @param mixed $data
     * @return void
     */
    public function add($data);

    /**
     * @return mixed
     */
    public function next();
}
