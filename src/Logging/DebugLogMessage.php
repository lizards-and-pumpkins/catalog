<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Logging;

class DebugLogMessage implements LogMessage
{
    /**
     * @var string
     */
    private $message;

    /**
     * @var mixed[]
     */
    private $context;

    /**
     * @param string $message
     * @param mixed[] $context
     */
    public function __construct(string $message, array $context = [])
    {
        $this->message = $message;
        $this->context = $context;
    }

    public function __toString() : string
    {
        return $this->message;
    }

    /**
     * @return mixed[]
     */
    public function getContext() : array
    {
        return $this->context;
    }

    public function getContextSynopsis() : string
    {
        $synopsis = array_map(function ($value) {
            return is_object($value) ?
                get_class($value) :
                $value;
        }, $this->context);

        return preg_replace('#\s{2,}#s', ' ', str_replace("\n", ' ', print_r($synopsis, true)));
    }
}
