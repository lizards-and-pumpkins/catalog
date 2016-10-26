<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Logging;

interface LogMessage
{
    public function __toString() : string;

    /**
     * @return mixed[]
     */
    public function getContext() : array;

    public function getContextSynopsis() : string;
}
