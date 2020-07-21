<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand\Command;

use LizardsAndPumpkins\ConsoleCommand\ConsoleCommand;
use LizardsAndPumpkins\Messaging\Command\CommandConsumer;
use LizardsAndPumpkins\Core\Factory\MasterFactory;

class ConsumeCommandsConsoleCommand implements ConsoleCommand
{
    /**
     * @var MasterFactory
     */
    private $masterFactory;

    public function __construct(MasterFactory $masterFactory)
    {
        $this->masterFactory = $masterFactory;
    }
    
    public function run()
    {
        /** @var CommandConsumer $commandConsumer */
        $commandConsumer = $this->masterFactory->createCommandConsumer();
        $commandConsumer->process();
    }
}
