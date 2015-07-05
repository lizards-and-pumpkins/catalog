<?php

namespace Brera;

class DomainCommandHandlerFailedMessage implements LogMessage
{
    /**
     * @var DomainCommand
     */
    private $domainCommand;

    /**
     * @var \Exception
     */
    private $exception;

    public function __construct(DomainCommand $domainCommand, \Exception $exception)
    {
        $this->domainCommand = $domainCommand;
        $this->exception = $exception;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            "Failure during processing %s domain command with following message:\n\n%s",
            get_class($this->domainCommand),
            $this->exception->getMessage()
        );
    }

    /**
     * @return mixed[]
     */
    public function getContext()
    {
        return ['exception' => $this->exception];
    }
}
