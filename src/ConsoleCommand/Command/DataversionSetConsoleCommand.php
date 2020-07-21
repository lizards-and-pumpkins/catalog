<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ConsoleCommand\Command;

use League\CLImate\CLImate;
use LizardsAndPumpkins\ConsoleCommand\BaseCliCommand;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\DataPool\DataVersion\SetCurrentDataVersionCommand;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Core\Factory\MasterFactory;

class DataversionSetConsoleCommand extends BaseCliCommand
{
    /**
     * @var MasterFactory|CommonFactory
     */
    private $factory;

    public function __construct(MasterFactory $factory, CLImate $CLImate)
    {
        $this->factory = $factory;
        $this->setCLImate($CLImate);
    }

    /**
     * @param CLImate $climate
     * @return array[]
     */
    final protected function getCommandLineArgumentsArray(CLImate $climate): array
    {
        return array_merge(parent::getCommandLineArgumentsArray($climate), [
            'processQueues'   => [
                'prefix'      => 'p',
                'longPrefix'  => 'processQueues',
                'description' => 'Process queues after adding the SetCurrentDataVersion command',
                'noValue'     => true,
            ],
            'dataVersion' => [
                'description' => 'The current data version to set.',
                'required'    => true,
            ],
        ]);
    }

    final protected function execute(CLImate $climate)
    {
        $this->addSetCurrentDataVersionCommand();
        $this->output(sprintf('Queued set-data-version command with version "%s"', $this->getArg('dataVersion')));
        $this->processQueuesIfRequested();
    }

    private function addSetCurrentDataVersionCommand()
    {
        $dataVersion = DataVersion::fromVersionString($this->getArg('dataVersion'));
        $commandQueue = $this->factory->getCommandQueue();
        $commandQueue->add(new SetCurrentDataVersionCommand($dataVersion));
    }

    private function processQueuesIfRequested()
    {
        if ($this->getArg('processQueues')) {
            $this->processQueues();
        }
    }

    private function processQueues()
    {
        $this->processCommandQueue();
        $this->processDomainEventQueue();
    }

    private function processCommandQueue()
    {
        $this->output('Processing command queue...');
        $commandConsumer = $this->factory->createCommandConsumer();
        $commandConsumer->processAll();
    }

    private function processDomainEventQueue()
    {
        $this->output('Processing domain event queue...');
        $domainEventConsumer = $this->factory->createDomainEventConsumer();
        $domainEventConsumer->processAll();
    }
}
