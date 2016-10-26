<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Util;

use League\CLImate\CLImate;

class StubCliCommand extends BaseCliCommand
{
    public $methodCalls = [];

    public function __construct(CLImate $climate)
    {
        $this->setCLImate($climate);
    }

    public function publicTestSetCLImate(CLImate $climate)
    {
        $this->setCLImate($climate);
    }

    public function publicTestGetCLImate() : CLImate
    {
        return $this->getCLImate();
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

    final protected function beforeExecute(CLImate $climate)
    {
        $this->methodCalls[] = __FUNCTION__;
        parent::beforeExecute($climate);
    }

    final protected function execute(CLImate $climate)
    {
        $this->methodCalls[] = __FUNCTION__;
    }

    final protected function afterExecute(CLImate $climate)
    {
        $this->methodCalls[] = __FUNCTION__;
        parent::afterExecute($climate);
    }

    public function publicTestOutput(string $string)
    {
        parent::output($string);
    }
}
