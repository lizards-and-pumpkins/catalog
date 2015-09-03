<?php

namespace Brera\Log;

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
