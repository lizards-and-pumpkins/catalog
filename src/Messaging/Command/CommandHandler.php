<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Messaging\Command;

interface CommandHandler
{
    /**
     * @return void
     */
    public function process();
}
