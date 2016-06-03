<?php

namespace LizardsAndPumpkins\Import;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Context\DataVersion\Exception\EmptyVersionException;
use LizardsAndPumpkins\Import\Exception\NoCatalogWasImportedDomainEventMessageException;
use LizardsAndPumpkins\Messaging\Event\DomainEvent;
use LizardsAndPumpkins\Messaging\Queue\Message;

class CatalogWasImportedDomainEvent implements DomainEvent
{
    const CODE = 'catalog_was_imported';
    
    /**
     * @var DataVersion
     */
    private $dataVersion;

    public function __construct(DataVersion $dataVersion)
    {
        $this->dataVersion = $dataVersion;
    }

    /**
     * @return DataVersion
     */
    public function getDataVersion()
    {
        return $this->dataVersion;
    }

    /**
     * @return Message
     */
    public function toMessage()
    {
        return Message::withCurrentTime(self::CODE, [], ['data_version' => (string) $this->dataVersion]);
    }

    /**
     * @param Message $message
     * @return static
     */
    public static function fromMessage(Message $message)
    {
        if ($message->getName() !== self::CODE) {
            throw new NoCatalogWasImportedDomainEventMessageException(
                sprintf('Expected "%s" domain event, got "%s"', self::CODE, $message->getName())
            );
        }
        return new self(DataVersion::fromVersionString($message->getMetadata()['data_version']));
    }
}
