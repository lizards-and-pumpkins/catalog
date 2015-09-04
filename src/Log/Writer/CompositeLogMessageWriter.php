<?php


namespace Brera\Log\Writer;

use Brera\Log\LogMessage;

class CompositeLogMessageWriter implements LogMessageWriter
{
    /**
     * @var LogMessageWriter[]
     */
    private $persisters;

    /**
     * @param LogMessageWriter[] $logMessagePersisterComponents
     */
    private function __construct(array $logMessagePersisterComponents)
    {
        $this->persisters = $logMessagePersisterComponents;
    }

    /**
     * @param LogMessageWriter $_ [optional]
     * @return CompositeLogMessageWriter
     */
    public static function fromParameterList()
    {
        $logMessagePersisters = func_get_args();
        array_map('self::validateIsLogMessagePersister', $logMessagePersisters);
        return new self($logMessagePersisters);
    }

    /**
     * @param mixed $persisterCandidate
     */
    private static function validateIsLogMessagePersister($persisterCandidate)
    {
        if (!is_object($persisterCandidate) || !$persisterCandidate instanceof LogMessageWriter) {
            $type = self::getTypeStringRepresentation($persisterCandidate);
            throw new NoLogMessagePersisterArgumentException(
                sprintf('The argument has to implement LogMessageWriter, got "%s"', $type)
            );
        }
    }

    /**
     * @param mixed $persisterCandidate
     * @return string
     */
    private static function getTypeStringRepresentation($persisterCandidate)
    {
        return is_object($persisterCandidate) ?
            get_class($persisterCandidate) :
            gettype($persisterCandidate);
    }

    public function persist(LogMessage $logMessage)
    {
        array_map(function (LogMessageWriter $persister) use ($logMessage) {
            $persister->persist($logMessage);
        }, $this->persisters);
    }
}
