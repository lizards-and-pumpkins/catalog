#!/usr/bin/env php
<?php

namespace LizardsAndPumpkins;

use League\CLImate\CLImate;
use LizardsAndPumpkins\Util\BaseCliCommand;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use LizardsAndPumpkins\Util\Factory\SampleMasterFactory;
use LizardsAndPumpkins\Util\Factory\TwentyOneRunFactory;

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} else {
    require_once __DIR__ . '/../../../autoload.php';
}

class ClearFileStorage extends BaseCliCommand
{
    /**
     * @var MasterFactory
     */
    private $factory;

    private function __construct(MasterFactory $factory, CLImate $climate)
    {
        $this->factory = $factory;
        $this->setCLImate($climate);
    }

    /**
     * @return ClearFileStorage
     */
    public static function bootstrap()
    {
        $factory = new SampleMasterFactory();
        $factory->register(new CommonFactory());
        $factory->register(new TwentyOneRunFactory());
        
        return new self($factory, new CLImate());
    }
    
    protected function execute(CLImate $climate)
    {
        $this->factory->createDataPoolWriter()->clear();
        $this->factory->createCommandQueue()->clear();
        $this->factory->createEventQueue()->clear();

        $this->output('Cleared data pool and queues');
        $this->output(sprintf("Storage dir: %s\n", $this->factory->getFileStorageBasePathConfig()));
    }
}

ClearFileStorage::bootstrap()->run();
