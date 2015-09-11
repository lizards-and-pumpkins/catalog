<?php


namespace Brera\Queue\Stub;

use Brera\Queue\Queue;
use Brera\Utils\Clearable;

class ClearableStubQueue implements Queue, Clearable
{
    public function clear()
    {
        // Intentionally left empty
    }

    public function count()
    {
        // Intentionally left empty
    }

    public function isReadyForNext()
    {
        // Intentionally left empty
    }

    /**
     * @param mixed $data
     */
    public function add($data)
    {
        // Intentionally left empty
    }

    public function next()
    {
        // Intentionally left empty
    }
}
