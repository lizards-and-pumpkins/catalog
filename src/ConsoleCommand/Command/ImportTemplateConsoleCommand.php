<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ConsoleCommand\Command;

use League\CLImate\CLImate;
use LizardsAndPumpkins\ConsoleCommand\BaseCliCommand;
use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\RootTemplate\Import\TemplateProjectorLocator;
use LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEvent;
use LizardsAndPumpkins\Util\Factory\CatalogMasterFactory;
use LizardsAndPumpkins\Util\Factory\MasterFactory;

class ImportTemplateConsoleCommand extends BaseCliCommand
{
    /**
     * @var CatalogMasterFactory
     */
    private $factory;

    public function __construct(MasterFactory $factory, CLImate $CLImate)
    {
        $this->factory = $factory;
        $this->setCLImate($CLImate);
    }
    
    /**
     * @param CLImate $climate
     * @return array[]
     */
    protected function getCommandLineArgumentsArray(CLImate $climate): array
    {
        return array_merge(
            parent::getCommandLineArgumentsArray($climate),
            [
                'processQueues' => [
                    'prefix'      => 'p',
                    'longPrefix'  => 'processQueues',
                    'description' => 'Process queues',
                    'noValue'     => true,
                ],
                'list'          => [
                    'prefix'      => 'l',
                    'longPrefix'  => 'list',
                    'description' => 'List available template IDs',
                    'noValue'     => true,
                ],
                'templateId'    => [
                    'description' => 'Template ID',
                    'required'    => false,
                ],
            ]
        );
    }

    protected function execute(CLImate $CLImate)
    {
        $this->isTemplateIdListRequested() ?
            $this->outputTemplateIdList() :
            $this->importSpecifiedTemplate();
    }

    private function importSpecifiedTemplate()
    {
        $this->addDomainEvent();
        $this->processQueuesIfRequested();
    }

    private function addDomainEvent()
    {
        $event = $this->createTemplateWasUpdatedEvent($this->getTemplateIdToProject());
        $this->factory->getEventQueue()->add($event);
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

    private function getTemplateIdToProject(): string
    {
        $templateId = $this->getArg('templateId');
        if (!in_array($templateId, $this->getValidTemplateIds())) {
            $message = $this->getInvalidTemplateIdMessage($templateId);
            throw new \InvalidArgumentException($message);
        }

        return $templateId;
    }

    private function getInvalidTemplateIdMessage(string $templateId): string
    {
        return sprintf(
            'Invalid template ID "%s". Valid template IDs are: %s',
            $templateId,
            implode(', ', $this->getValidTemplateIds())
        );
    }

    /**
     * @return string[]
     */
    private function getValidTemplateIds(): array
    {
        /** @var TemplateProjectorLocator $templateProjectorLocator */
        $templateProjectorLocator = $this->factory->createTemplateProjectorLocator();

        return $templateProjectorLocator->getRegisteredProjectorCodes();
    }

    protected function isTemplateIdListRequested(): bool
    {
        return (bool) $this->getArg('list');
    }

    protected function outputTemplateIdList()
    {
        $this->output('Available template IDs:');
        $this->output(implode(PHP_EOL, $this->getValidTemplateIds()));
    }

    private function createTemplateWasUpdatedEvent(string $templateId): TemplateWasUpdatedDomainEvent
    {
        $projectionSourceData = '';
        $currentDataVersion = $this->factory->createDataPoolReader()->getCurrentDataVersion();
        $dataVersion = DataVersion::fromVersionString($currentDataVersion);

        return new TemplateWasUpdatedDomainEvent($templateId, $projectionSourceData, $dataVersion);
    }
}
