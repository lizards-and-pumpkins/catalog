<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand\TestDouble;

use League\CLImate\CLImate;
use LizardsAndPumpkins\ConsoleCommand\BaseCliCommand;
use LizardsAndPumpkins\Core\Factory\MasterFactory;

class MockCliCommand extends BaseCliCommand
{
    /**
     * @var MasterFactory
     */
    public $factory;

    /**
     * @var CLImate
     */
    public $cliMate;

    public function __construct(MasterFactory $factory, CLImate $cliMate)
    {
        $this->factory = $factory;
        $this->cliMate = $cliMate;
    }

    /**
     * @param CLImate $climate
     * @return void
     */
    protected function execute(CLImate $climate)
    {
        // left empty on purpose since this is just a test double
    }
}
