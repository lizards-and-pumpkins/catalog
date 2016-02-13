#!/usr/bin/env php
<?php

namespace LizardsAndPumpkins;

use League\CLImate\CLImate;
use LizardsAndPumpkins\Projection\LoggingDomainEventHandlerFactory;
use LizardsAndPumpkins\Projection\TemplateWasUpdatedDomainEvent;
use LizardsAndPumpkins\Queue\Queue;
use LizardsAndPumpkins\Utils\BaseCliCommand;

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    require_once __DIR__ . '/../../../autoload.php';
}

class TriggerTemplateUpdate extends BaseCliCommand
{
    /**
     * @var MasterFactory
     */
    private $factory;

    private function __construct(MasterFactory $factory, CLImate $CLImate)
    {
        $this->factory = $factory;
        $this->setCLImate($CLImate);
    }

    /**
     * @return RunImport
     */
    public static function bootstrap()
    {
        $factory = new SampleMasterFactory();
        $factory->register(new CommonFactory());
        $factory->register(new TwentyOneRunFactory());
        $factory->register(new LoggingDomainEventHandlerFactory());

        return new self($factory, new CLImate());
    }

    /**
     * @param CLImate $climate
     * @return array[]
     */
    protected function getCommandLineArgumentsArray(CLImate $climate)
    {
        return array_merge(parent::getCommandLineArgumentsArray($climate), [
            'processQueues' => [
                'prefix' => 'p',
                'longPrefix' => 'processQueues',
                'description' => 'Process queues',
                'noValue' => true,
            ],
            'templateId' => [
                'description' => 'Template ID',
                'required' => true
            ]
        ]);
    }

    protected function execute(CLImate $CLImate)
    {
        $this->addDomainEvent();
        $this->processQueuesIfRequested();
    }

    private function addDomainEvent()
    {
        $templateId = $this->getArg('templateId');
        $projectionSourceData = '';

        $this->factory->getEventQueue()->add(new TemplateWasUpdatedDomainEvent($templateId, $projectionSourceData));
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
        $this->processQueueWhileMessagesPending(
            $this->factory->getCommandQueue(),
            $this->factory->createCommandConsumer()
        );
    }

    private function processDomainEventQueue()
    {
        $this->output('Processing domain event queue...');
        $this->processQueueWhileMessagesPending(
            $this->factory->getEventQueue(),
            $this->factory->createDomainEventConsumer()
        );
    }

    private function processQueueWhileMessagesPending(Queue $queue, QueueMessageConsumer $consumer)
    {
        while ($queue->count()) {
            $consumer->process();
        }
    }
}

TriggerTemplateUpdate::bootstrap()->run();
