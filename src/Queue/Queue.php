<?php

namespace Brera\Queue;

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
     * @return null
     */
    public function add($data);

    /**
     * @return mixed
     */
    public function next();
}
