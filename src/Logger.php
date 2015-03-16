<?php

namespace Brera;

interface Logger
{
    /**
     * @param LogMessage $message
     */
    public function log(LogMessage $message);

    /**
     * @return LogMessage[]
     */
    public function getMessages();
}
