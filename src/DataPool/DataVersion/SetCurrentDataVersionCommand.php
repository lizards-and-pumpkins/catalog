<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool\DataVersion;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\DataPool\DataVersion\Exception\NotSetCurrentDataVersionCommandMessageException;
use LizardsAndPumpkins\Messaging\Command\Command;
use LizardsAndPumpkins\Messaging\Queue\Message;

class SetCurrentDataVersionCommand implements Command
{
    const CODE = 'set_current_data_version'; 
    
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
        $metadata = ['data_version' => (string) $this->dataVersion];
        return Message::withCurrentTime(self::CODE, $payload, $metadata);
    }

    public static function fromMessage(Message $message): self
    {
        if ($message->getName() !== self::CODE) {
            $exceptionMessage = sprintf('Expected message name %s, got "%s"', self::CODE, $message->getName());
            throw new NotSetCurrentDataVersionCommandMessageException($exceptionMessage);
        }
        return new self(DataVersion::fromVersionString($message->getMetadata()['data_version']));
    }

    public function getDataVersion(): DataVersion
    {
        return $this->dataVersion;
    }
}
