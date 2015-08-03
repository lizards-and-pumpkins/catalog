<?php

namespace Brera\Queue;

use Brera\LogMessage;

class QueueProcessingLimitIsReachedMessage implements LogMessage
{
    /**
     * @var string
     */
    private $queueClassName;

    /**
     * @var int
     */
    private $processingLimit;

    /**
     * @param string $queueClassName
     * @param int $processingLimit
     */
    public function __construct($queueClassName, $processingLimit)
    {
        $this->queueClassName = $queueClassName;
        $this->processingLimit = $processingLimit;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s has reached processing limit of %d.', $this->queueClassName, $this->processingLimit);
    }

    /**
     * @return mixed[]
     */
    public function getContext()
    {
        return [];
    }
}
