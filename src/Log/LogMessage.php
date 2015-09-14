<?php

namespace LizardsAndPumpkins\Log;

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
}
