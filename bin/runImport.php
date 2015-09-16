#!/usr/bin/env php
<?php

namespace LizardsAndPumpkins;

use League\CLImate\CLImate;
use LizardsAndPumpkins\DataPool\DataPoolWriter;
use LizardsAndPumpkins\Projection\Catalog\Import\CatalogImport;
use LizardsAndPumpkins\Queue\Queue;

require_once __DIR__ . '/../vendor/autoload.php';

class RunImport
{
    /**
     * @var MasterFactory
     */
    private $factory;
    
    /**
     * @var CLImate
     */
    private $climate;

    private function __construct(MasterFactory $factory, CLImate $CLImate)
    {
        $this->factory = $factory;
        $this->climate = $CLImate;
    }

    /**
     * @return RunImport
     */
    public static function bootstrap()
    {
        $factory = new SampleMasterFactory();
        $factory->register(new CommonFactory());
        $factory->register(new SampleFactory());

        return new self($factory, new CLImate());
    }
    
    public function run()
    {
        try {
            $this->prepareCommandLineArguments();
            $this->execute();
        } catch (\Exception $e) {
            $this->climate->error($e->getMessage());
            $this->climate->error(sprintf('%s:%d', $e->getFile(), $e->getLine()));
            $this->climate->usage();
        }
    }

    private function prepareCommandLineArguments()
    {
        $this->climate->arguments->add([
            'clearStorage' => [
                'prefix' => 'c',
                'longPrefix' => 'clearStorage',
                'description' => 'Clear queues and data pool before the import)',
                'noValue' => true,
            ],
            'processQueues' => [
                'prefix' => 'p',
                'longPrefix' => 'processQueues',
                'description' => 'Process queues after the import)',
                'noValue' => true,
            ],
            'environmentConfig' => [
                'prefix' => 'e',
                'longPrefix' => 'environmentConfig',
                'description' => 'Environment config settings, comma separated [foo=bar,baz=qux]',
            ],
            'importFile' => [
                'description' => 'Import XML file',
                'required' => true
            ]
        ]);
        
        $this->validateArguments();
    }

    private function validateArguments()
    {
        $this->climate->arguments->parse();
        $env = $this->getArg('environmentConfig');
        if ($env) {
            $this->applyEnvironmentConfigSettings($env);
        }
    }

    private function execute()
    {
        $this->clearStorageIfRequested();
        $this->importFile();
        $this->processQueuesIfRequested();
    }

    private function clearStorageIfRequested()
    {
        if ($this->getArg('clearStorage')) {
            $this->clearStorage();
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

    /**
     * @param Queue $queue
     * @param CommandConsumer|DomainEventConsumer $consumer
     */
    private function processQueueWhileMessagesPending(Queue $queue, $consumer)
    {
        while ($queue->count()) {
            $consumer->process();
        }
    }

    /**
     * @param string $arg
     * @return bool|float|int|null|string
     */
    private function getArg($arg)
    {
        return $this->climate->arguments->get($arg);
    }

    /**
     * @param string $message
     * @return mixed
     */
    private function output($message)
    {
        return $this->climate->output($message);
    }

    /**
     * @param string $environmentConfigSettingsString
     */
    private function applyEnvironmentConfigSettings($environmentConfigSettingsString)
    {
        array_map(function ($setting) {
            @list($key, $value) = explode('=', $setting, 2);
            if (trim($key)) {
                $_SERVER[EnvironmentConfigReader::ENV_VAR_PREFIX . strtoupper(trim($key))] = trim($value);
            }
        }, explode(',', $environmentConfigSettingsString));
    }
}

RunImport::bootstrap()->run();
