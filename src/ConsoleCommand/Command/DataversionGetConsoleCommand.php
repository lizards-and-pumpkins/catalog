<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ConsoleCommand\Command;

use League\CLImate\CLImate;
use LizardsAndPumpkins\ConsoleCommand\BaseCliCommand;
use LizardsAndPumpkins\DataPool\DataPoolReader;
use LizardsAndPumpkins\Util\Factory\CommonFactory;
use LizardsAndPumpkins\Core\Factory\MasterFactory;

class DataversionGetConsoleCommand extends BaseCliCommand
{
    /**
     * @var MasterFactory|CommonFactory
     */
    private $factory;

    public function __construct(MasterFactory $factory, CLImate $CLImate)
    {
        $this->factory = $factory;
        $this->setCLImate($CLImate);
    }

    protected function execute(CLImate $climate): void
    {
        $this->getCLImate()->output(sprintf('Current data version:  %s', $this->getCurrentDataVersion()));
        $this->getCLImate()->output(sprintf('Previous data version: %s', $this->getPreviousDataVersion()));
    }

    private function getCurrentDataVersion(): string
    {
        return $this->getDataPoolReader()->getCurrentDataVersion();
    }

    private function getPreviousDataVersion(): string
    {
        return $this->getDataPoolReader()->getPreviousDataVersion();
    }

    private function getDataPoolReader(): DataPoolReader
    {
        return $this->factory->createDataPoolReader();
    }
}
