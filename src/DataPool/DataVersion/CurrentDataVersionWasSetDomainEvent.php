<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\DataVersion;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\DataPool\DataVersion\Exception\NotCurrentDataVersionWasSetMessageException;
use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\Messaging\Queue\Message;

class CurrentDataVersionWasSetDomainEvent implements DomainEvent
{
    const CODE = 'current_data_version_was_set';
    
    /**
     * @var DataVersion
     */
    private $dataVersion;

    public function __construct(DataVersion $dataVersion)
    {
        $this->dataVersion = $dataVersion;
    }

    public function toMessage(): Message
    {
        $payload = [];
        $metadata = [DataVersion::VERSION_KEY => (string) $this->dataVersion];
        return Message::withCurrentTime(self::CODE, $payload, $metadata);
    }

    public static function fromMessage(Message $message): self
    {
        if ($message->getName() !== self::CODE) {
            $message = sprintf('Message name "%s" does not match %s', $message->getName(), CurrentDataVersionWasSetDomainEvent::CODE);
            throw new NotCurrentDataVersionWasSetMessageException($message);
        }

        return new self(DataVersion::fromVersionString($message->getMetadata()[DataVersion::VERSION_KEY]));
    }

    public function getDataVersion(): DataVersion
    {
        return $this->dataVersion;
    }
}
