<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand\Command;

use League\CLImate\CLImate;
use LizardsAndPumpkins\ConsoleCommand\BaseCliCommand;
use LizardsAndPumpkins\Messaging\Consumer\ShutdownWorkerDirective;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use LizardsAndPumpkins\Messaging\Command\CommandQueue;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;

class ShutdownConsumerConsoleCommand extends BaseCliCommand
{
    const COMMAND_CONSUMER = 'command';
    const EVENT_CONSUMER = 'event';

    /**
     * @var MasterFactory
     */
    private $factory;

    public function __construct(MasterFactory $factory, CLImate $climate)
    {
        $this->factory = $factory;
        $this->setCLImate($climate);
    }
    
    /**
     * @param CLImate $climate
     * @return array[]
     */
    final protected function getCommandLineArgumentsArray(CLImate $climate): array
    {
        return array_merge(
            parent::getCommandLineArgumentsArray($climate),
            [
                'quiet' => [
                    'prefix' => 'q',
                    'longPrefix' => 'quiet',
                    'description' => 'No output',
                    'noValue' => true
                ],
                'type' => [
                    'description' => '"command" or "event"',
                    'required'    => true,
                ],
                'pid'  => [
                    'description'  => 'numeric PID',
                    'required'     => true,
                ],
            ]
        );
    }

    final protected function execute(CLImate $climate)
    {
        $queue = $this->selectQueue();
        $queue->add($this->createShutdownDirective());
        $this->displayMessage();
    }

    private function type() : string
    {
        $type = $this->getArg('type');
        if (self::COMMAND_CONSUMER !== $type && self::EVENT_CONSUMER !== $type) {
            throw new \InvalidArgumentException('Type must be "command" or "event"');
        }

        return $type;
    }

    private function pid() : int
    {
        return (int) $this->getArg('pid');
    }

    /**
     * @return CommandQueue|DomainEventQueue
     */
    private function selectQueue()
    {
        return $this->type() === self::COMMAND_CONSUMER ?
            $this->factory->getCommandQueue() :
            $this->factory->getEventQueue();
    }

    private function createShutdownDirective(): ShutdownWorkerDirective
    {
        return new ShutdownWorkerDirective((string) $this->pid());
    }

    private function displayMessage()
    {
        if (!$this->getArg('quiet')) {
            $format = 'Shutdown directive for %s consumer with pid "%s" added';
            $this->output(sprintf($format, $this->type(), $this->pid()));
        }
    }
}
