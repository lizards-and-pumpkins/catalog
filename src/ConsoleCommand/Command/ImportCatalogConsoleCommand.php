<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\ConsoleCommand\Command;

use League\CLImate\CLImate;
use LizardsAndPumpkins\ConsoleCommand\BaseCliCommand;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\CatalogImport;
use LizardsAndPumpkins\Import\Image\NullProductImageImportCommandFactory;
use LizardsAndPumpkins\Import\Image\UpdatingProductImageImportCommandFactory;
use LizardsAndPumpkins\ProductDetail\Import\UpdatingProductImportCommandFactory;
use LizardsAndPumpkins\ProductListing\Import\UpdatingProductListingImportCommandFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;
use LizardsAndPumpkins\Util\Factory\CatalogMasterFactory;

class ImportCatalogConsoleCommand extends BaseCliCommand
{
    /**
     * @var CatalogMasterFactory
     */
    private $factory;

    public function __construct(MasterFactory $factory, CLImate $CLImate)
    {
        $this->factory = $factory;
        $this->factory->register(new UpdatingProductImportCommandFactory());
        $this->factory->register(new UpdatingProductListingImportCommandFactory());
        $this->setCLImate($CLImate);
    }

    /**
     * @param CLImate $climate
     * @return array[]
     */
    final protected function getCommandLineArgumentsArray(CLImate $climate): array
    {
        return array_merge(
            parent::getCommandLineArgumentsArray($climate),
            [
                'clearStorage'  => [
                    'prefix'      => 'c',
                    'longPrefix'  => 'clearStorage',
                    'description' => 'Clear queues and data pool before the import',
                    'noValue'     => true,
                ],
                'processQueues' => [
                    'prefix'      => 'p',
                    'longPrefix'  => 'processQueues',
                    'description' => 'Process queues after the import',
                    'noValue'     => true,
                ],
                'importImages'  => [
                    'prefix'      => 'i',
                    'longPrefix'  => 'importImages',
                    'description' => 'Process images during import',
                    'noValue'     => true,
                ],
                'dataVersion'   => [
                    'prefix'      => 'd',
                    'longPrefix'  => 'dataVersion',
                    'description' => 'Data version to associate with the catalog data (defaults to current version)',
                    'noValue'     => true,
                ],
                'importFile'    => [
                    'description' => 'Import XML file',
                    'required'    => true,
                ],
            ]
        );
    }

    final protected function execute(CLImate $CLImate)
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

        $dataPoolWriter = $this->factory->createDataPoolWriter();
        $dataPoolWriter->clear();
    }

    private function importFile()
    {
        $this->output('Importing...');

        /** @var CatalogImport $import */
        $import = $this->factory->createCatalogImport();
        $import->importFile($this->getArg('importFile'), DataVersion::fromVersionString($this->getDataVersion()));
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
        $commandConsumer = $this->factory->createCommandConsumer();
        $commandConsumer->processAll();
    }

    private function processDomainEventQueue()
    {
        $this->output('Processing domain event queue...');
        $domainEventConsumer = $this->factory->createDomainEventConsumer();
        $domainEventConsumer->processAll();
    }

    private function getDataVersion(): string
    {
        return $this->getArg('dataVersion') ?? $this->factory->createDataPoolReader()->getCurrentDataVersion();
    }
}
