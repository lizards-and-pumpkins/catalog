<?php

namespace LizardsAndPumpkins\Log;

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
    public function __construct($message, array $context = [])
    {
        $this->message = $message;
        $this->context = $context;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->message;
    }

    /**
     * @return mixed[]
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return string
     */
    public function getContextSynopsis()
    {
        $synopsis = array_map(function ($value) {
            return is_object($value) ?
                get_class($value) :
                $value;
        }, $this->context);
        return preg_replace('#\s{2,}#s', ' ', str_replace("\n", ' ', print_r($synopsis, true)));
    }
}
