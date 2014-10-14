<?php

namespace Brera\PoC;

interface Logger
{
    /**
     * @param LogMessage $message
     * @return null
     */
    public function log(LogMessage $message);
} 