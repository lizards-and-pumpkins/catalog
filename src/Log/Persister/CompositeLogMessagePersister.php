<?php


namespace Brera\Log\Persister;

use Brera\Log\LogMessage;

class CompositeLogMessagePersister implements LogMessagePersister
{
    /**
     * @var LogMessagePersister[]
     */
    private $persisters;

    /**
     * @param LogMessagePersister[] $logMessagePersisterComponents
     */
    private function __construct(array $logMessagePersisterComponents)
    {
        $this->persisters = $logMessagePersisterComponents;
    }

    /**
     * @param LogMessagePersister $_ [optional]
     * @return CompositeLogMessagePersister
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
        if (!is_object($persisterCandidate) || !$persisterCandidate instanceof LogMessagePersister) {
            $type = self::getTypeStringRepresentation($persisterCandidate);
            throw new NoLogMessagePersisterArgumentException(
                sprintf('The argument has to implement LogMessagePersister, got "%s"', $type)
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
        array_map(function (LogMessagePersister $persister) use ($logMessage) {
            $persister->persist($logMessage);
        }, $this->persisters);
    }
}
