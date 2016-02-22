#!/usr/bin/env php
<?php

namespace LizardsAndPumpkins;

use League\CLImate\CLImate;
use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Projection\Catalog\Import\CatalogImport;
use LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand\NullProductImageImportCommandFactory;
use LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand\UpdatingProductImageImportCommandFactory;
use LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand\UpdatingProductImportCommandFactory;
use LizardsAndPumpkins\Projection\Catalog\Import\ImportCommand\UpdatingProductListingImportCommandFactory;
use LizardsAndPumpkins\Projection\LoggingDomainEventHandlerFactory;
use LizardsAndPumpkins\Queue\Queue;
use LizardsAndPumpkins\Utils\BaseCliCommand;

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    require_once __DIR__ . '/../../../autoload.php';
}

class RunImport extends BaseCliCommand
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
        $commonFactory = new CommonFactory();
        $implementationFactory = new TwentyOneRunFactory();
        $factory->register($commonFactory);
        $factory->register($implementationFactory);
        $factory->register(new UpdatingProductImportCommandFactory());
//        $factory->register(new LoggingDomainEventHandlerFactory($commonFactory));
//        $factory->register(new UpdatingProductListingImportCommandFactory());

        return new self($factory, new CLImate());
    }

    /**
     * @param CLImate $climate
     * @return array[]
     */
    protected function getCommandLineArgumentsArray(CLImate $climate)
    {
        return array_merge(parent::getCommandLineArgumentsArray($climate), [
            'clearStorage' => [
                'prefix' => 'c',
                'longPrefix' => 'clearStorage',
                'description' => 'Clear queues and data pool before the import',
                'noValue' => true,
            ],
            'processQueues' => [
                'prefix' => 'p',
                'longPrefix' => 'processQueues',
                'description' => 'Process queues after the import',
                'noValue' => true,
            ],
            'importImages' => [
                'prefix' => 'i',
                'longPrefix' => 'importImages',
                'description' => 'Process images during import',
                'noValue' => true,
            ],
            'importFile' => [
                'description' => 'Import XML file',
                'required' => true
            ]
        ]);
    }


    protected function execute(CLImate $CLImate)
    {
        $this->clearStorageIfRequested();
        $this->enableImageImportIfRequested();
        $this->importFile();
        $this->processQueuesIfRequested();
    }

    private function clearStorageIfRequested()
    {
        if ($this->getArg('clearStorage')) {
            $this->clearStorage();
        }
    }

    private function enableImageImportIfRequested()
    {
        if ($this->getArg('importImages')) {
            $this->factory->register(new UpdatingProductImageImportCommandFactory());
        } else {
            $this->factory->register(new NullProductImageImportCommandFactory());
        }
    }

    private function clearStorage()
    {
        $this->output('Clearing queue and data pool before import...');

        /** @var DataPoolWriter $dataPoolWriter */
        $dataPoolWriter = $this->factory->createDataPoolWriter();
        $dataPoolWriter->clear();
    }

    private function importFile()
    {
        $this->output('Importing...');

        /** @var CatalogImport $import */
        $import = $this->factory->createCatalogImport();
        $import->importFile($this->getArg('importFile'));
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

RunImport::bootstrap()->run();
