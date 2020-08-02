<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ConsoleCommand\TestDouble;

use League\CLImate\CLImate;
use LizardsAndPumpkins\ConsoleCommand\BaseCliCommand;

class StubCliCommand extends BaseCliCommand
{
    public $methodCalls = [];

    public function __construct(CLImate $climate)
    {
        $this->setCLImate($climate);
    }

    public function publicTestSetCLImate(CLImate $climate): void
    {
        $this->setCLImate($climate);
    }

    public function publicTestGetCLImate() : CLImate
    {
        return $this->getCLImate();
    }

    /**
     * @param string[] $argv
     */
    public function publicSetArgumentVector(array $argv): void
    {
        $this->setArgumentVector($argv);
    }

    /**
     * @param CLImate $climate
     * @return array[]
     */
    final protected function getCommandLineArgumentsArray(CLImate $climate) : array
    {
        $this->methodCalls[] = __FUNCTION__;
        return parent::getCommandLineArgumentsArray($climate);
    }

    final protected function beforeExecute(CLImate $climate): void
    {
        $this->methodCalls[] = __FUNCTION__;
        parent::beforeExecute($climate);
    }

    final protected function execute(CLImate $climate): void
    {
        $this->methodCalls[] = __FUNCTION__;
    }

    final protected function afterExecute(CLImate $climate): void
    {
        $this->methodCalls[] = __FUNCTION__;
        parent::afterExecute($climate);
    }

    public function publicTestOutput(string $string): void
    {
        parent::output($string);
    }
}
