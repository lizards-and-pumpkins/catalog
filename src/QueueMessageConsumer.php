<?php

namespace LizardsAndPumpkins;

interface QueueMessageConsumer
{
    /**
     * @return void
     */
    public function process();
}
