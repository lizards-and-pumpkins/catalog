<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Import\RootTemplate;

use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue\Message;

class UpdateTemplateCommandHandler implements CommandHandler
{
    /**
     * @var UpdateTemplateCommand
     */
    private $command;

    /**
     * @var DomainEventQueue
     */
    private $domainEventQueue;

    public function __construct(Message $message, DomainEventQueue $domainEventQueue)
    {
        $this->command = UpdateTemplateCommand::fromMessage($message);
        $this->domainEventQueue = $domainEventQueue;
    }

    public function process()
    {
        $templateId = $this->command->getTemplateId();
        $templateContent = $this->command->getTemplateContent();
        $dataVersion = $this->command->getDataVersion();
        $this->domainEventQueue->add(new TemplateWasUpdatedDomainEvent($templateId, $templateContent, $dataVersion));
    }
}
