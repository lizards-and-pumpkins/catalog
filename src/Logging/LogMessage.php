<?php

namespace LizardsAndPumpkins\Logging;

interface LogMessage
{
    /**
     * @return string
     */
    public function __toString();

    /**
     * @return mixed[]
     */
    public function getContext();

    /**
     * @return string
     */
    public function getContextSynopsis();
}
