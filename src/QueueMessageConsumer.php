<?php


namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Queue\Queue;

interface QueueMessageConsumer
{
    /**
     * @return void
     */
    public function process();
}
