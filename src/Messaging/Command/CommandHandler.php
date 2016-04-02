<?php

namespace LizardsAndPumpkins\Messaging\Command;

interface CommandHandler
{
    /**
     * @return void
     */
    public function process();
}
