<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\Import\RootTemplate;

use LizardsAndPumpkins\Messaging\Command\CommandHandler;
use LizardsAndPumpkins\Messaging\Event\DomainEventQueue;
use LizardsAndPumpkins\Messaging\Queue\Message;

class UpdateTemplateCommandHandler implements CommandHandler
{
    /**
     * @var DomainEventQueue
     */
    private $domainEventQueue;

    public function __construct(DomainEventQueue $domainEventQueue)
    {
        $this->domainEventQueue = $domainEventQueue;
    }

    public function process(Message $message)
    {
        $command = UpdateTemplateCommand::fromMessage($message);
        $templateId = $command->getTemplateId();
        $templateContent = $command->getTemplateContent();
        $dataVersion = $command->getDataVersion();
        $this->domainEventQueue->add(new TemplateWasUpdatedDomainEvent($templateId, $templateContent, $dataVersion));
    }
}
