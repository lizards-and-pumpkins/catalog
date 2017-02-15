<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand;

use League\CLImate\CLImate;
use LizardsAndPumpkins\Util\Config\EnvironmentConfigReader;

abstract class BaseCliCommand implements ConsoleCommand
{
    /**
     * @var CLImate
     */
    private $climate;

    /**
     * @var string[]
     */
    private $argv;

    final protected function setCLImate(CLImate $climate)
    {
        $this->climate = $climate;
    }

    final protected function getCLImate(): CLImate
    {
        if (null === $this->climate) {
            $this->setCLImate(new CLImate());
        }

        return $this->climate;
    }

    /**
     * @param string[] $argv
     */
    final protected function setArgumentVector(array $argv)
    {
        $this->argv = $argv;
    }

    /**
     * @return string[]
     */
    private function getArgumentVector(): array
    {
        if (null === $this->argv) {
            global $argv;
            return $argv;
        }
        return $this->argv;
    }

    public function run()
    {
        try {
            $this->handleHookMethodFlow();
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    private function handleHookMethodFlow()
    {
        $climate = $this->getCLImate();
        $this->prepareCommandLineArguments($climate);

        if ($this->getArg('help')) {
            $climate->usage($this->getArgumentVectorWithCommandName());
        } else {
            $this->processBeforeExecute();
            $this->execute($climate);
            $this->processAfterExecute();
        }
    }

    private function handleException(\Exception $e)
    {
        $climate = $this->getCLImate();
        $climate->error($e->getMessage());
        $climate->error(sprintf('%s:%d', $e->getFile(), $e->getLine()));
        $climate->usage($this->getArgumentVectorWithCommandName());
    }

    private function prepareCommandLineArguments(CLImate $climate)
    {
        $arguments = $this->getCommandLineArgumentsArray($climate);
        $climate->arguments->add($arguments);
        
        $climate->arguments->parse($this->getArgumentVectorWithCommandName());
    }

    private function getArgumentVectorWithCommandName()
    {
        $argv = $this->getArgumentVector();
        
        $argvWithoutCommandName = [];
        foreach ($argv as $i => $value) {
            if (1 === $i) {
                $argvWithoutCommandName[0] .= ' ' . $value;
                continue;
            }
            $argvWithoutCommandName[] = $value;
        }
        return $argvWithoutCommandName;
    }

    /**
     * @param CLImate $climate
     * @return array[]
     */
    protected function getCommandLineArgumentsArray(CLImate $climate): array
    {
        return [
            'help'              => [
                'prefix'      => 'h',
                'longPrefix'  => 'help',
                'description' => 'Usage help',
                'noValue'     => true,
            ],
        ];
    }

    private function processBeforeExecute()
    {
        $this->beforeExecute($this->getCLImate());
    }

    protected function beforeExecute(CLImate $climate)
    {
        // Intentionally empty hook method
    }

    /**
     * @param CLImate $climate
     * @return void
     */
    abstract protected function execute(CLImate $climate);

    private function processAfterExecute()
    {
        $this->afterExecute($this->getCLImate());
    }

    protected function afterExecute(CLImate $climate)
    {
        // Intentionally empty hook method
    }

    /**
     * @param string $arg
     * @return bool|float|int|null|string
     */
    final protected function getArg(string $arg)
    {
        return $this->getCLImate()->arguments->get($arg);
    }

    /**
     * @param string $message
     * @return mixed
     */
    final protected function output(string $message)
    {
        return $this->getCLImate()->output($message);
    }
}
