<?php


namespace LizardsAndPumpkins\Utils\Stub;

use League\CLImate\CLImate;
use LizardsAndPumpkins\Utils\BaseCliCommand;

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

    /**
     * @return CLImate
     */
    public function publicTestGetCLImate()
    {
        return $this->getCLImate();
    }

    /**
     * @param CLImate $climate
     * @return array[]
     */
    protected function getCommandLineArgumentsArray(CLImate $climate)
    {
        $this->methodCalls[] = __FUNCTION__;
        return parent::getCommandLineArgumentsArray($climate);
    }

    protected function beforeExecute(CLImate $climate)
    {
        $this->methodCalls[] = __FUNCTION__;
        parent::beforeExecute($climate);
    }

    protected function execute(CLImate $climate)
    {
        $this->methodCalls[] = __FUNCTION__;
    }

    protected function afterExecute(CLImate $climate)
    {
        $this->methodCalls[] = __FUNCTION__;
        parent::afterExecute($climate);
    }

    /**
     * @param string $string
     */
    public function publicTestOutput($string)
    {
        parent::output($string);
    }
}
