<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand\Command;

use League\CLImate\CLImate;
use LizardsAndPumpkins\ConsoleCommand\BaseCliCommand;
use LizardsAndPumpkins\Messaging\Queue;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class ReportQueueCountConsoleCommand extends BaseCliCommand
{
    /**
     * @var MasterFactory
     */
    private $masterFactory;

    public function __construct(MasterFactory $factory, CLImate $climate)
    {
        $this->masterFactory = $factory;
        $this->setCLImate($climate);
    }

    protected function execute(CLImate $climate)
    {
        $tableData = $this->formatTableData(
            $this->masterFactory->getCommandMessageQueue(),
            $this->masterFactory->getEventMessageQueue()
        );
        $climate->table($tableData);
    }

    /**
     * @param Queue $commandQueue
     * @param Queue $eventQueue
     * @return string[]
     */
    private function formatTableData(Queue $commandQueue, Queue $eventQueue): array
    {
        return [
            [
                'Queue' => 'Command',
                'Count' => sprintf('%10d', $commandQueue->count()),
            ],
            [
                'Queue' => 'Event',
                'Count' => sprintf('%10d', $eventQueue->count()),
            ],
        ];
    }
}
