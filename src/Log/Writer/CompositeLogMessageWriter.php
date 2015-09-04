<?php


namespace Brera\Log\Writer;

use Brera\Log\LogMessage;

class CompositeLogMessageWriter implements LogMessageWriter
{
    /**
     * @var LogMessageWriter[]
     */
    private $writers;

    /**
     * @param LogMessageWriter[] $logMessageWriters
     */
    private function __construct(array $logMessageWriters)
    {
        $this->writers = $logMessageWriters;
    }

    /**
     * @param LogMessageWriter $_ [optional]
     * @return CompositeLogMessageWriter
     */
    public static function fromParameterList()
    {
        $logMessageWriters = func_get_args();
        array_map('self::validateIsLogMessageWriter', $logMessageWriters);
        return new self($logMessageWriters);
    }

    /**
     * @param mixed $writerCandidate
     */
    private static function validateIsLogMessageWriter($writerCandidate)
    {
        if (!is_object($writerCandidate) || !$writerCandidate instanceof LogMessageWriter) {
            $type = self::getTypeStringRepresentation($writerCandidate);
            throw new NoLogMessageWriterArgumentException(
                sprintf('The argument has to implement LogMessageWriter, got "%s"', $type)
            );
        }
    }

    /**
     * @param mixed $writerCandidate
     * @return string
     */
    private static function getTypeStringRepresentation($writerCandidate)
    {
        return is_object($writerCandidate) ?
            get_class($writerCandidate) :
            gettype($writerCandidate);
    }

    public function write(LogMessage $logMessage)
    {
        array_map(function (LogMessageWriter $writer) use ($logMessage) {
            $writer->write($logMessage);
        }, $this->writers);
    }
}
