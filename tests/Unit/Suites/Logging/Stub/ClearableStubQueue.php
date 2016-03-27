<?php


namespace LizardsAndPumpkins\Logging\Stub;

use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Util\Storage\Clearable;

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
