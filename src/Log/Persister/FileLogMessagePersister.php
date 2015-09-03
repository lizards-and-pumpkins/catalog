<?php


namespace Brera\Log\Persister;

use Brera\Log\LogMessage;

class FileLogMessagePersister implements LogMessagePersister
{
    /**
     * @var string
     */
    private $logFilePath;

    /**
     * @param string $logFilePath
     */
    public function __construct($logFilePath)
    {
        $this->logFilePath = $logFilePath;
    }

    public function persist(LogMessage $logMessage)
    {
        $this->createLogDirIfNotExists();
        $this->validateLogFileIsWritable();
        $this->logMessageToFile($logMessage);
    }

    private function createLogDirIfNotExists()
    {
        $logDirPath = dirname($this->logFilePath);
        if (!file_exists($logDirPath)) {
            $this->createLogDir($logDirPath);
        }
    }

    /**
     * @param string $logDirPath
     */
    private function createLogDir($logDirPath)
    {
        try {
            mkdir($logDirPath, 0700, true);
        } catch (\Exception $e) {
            throw new UnableToCreateLogDirectoryException($e->getMessage());
        }
    }

    private function validateLogFileIsWritable()
    {
        if (!is_writable(dirname($this->logFilePath))) {
            throw new UnableToCreateLogFileException(
                sprintf('The log directory is not writable: "%s"', dirname($this->logFilePath))
            );
        }
        if (file_exists($this->logFilePath) && !is_writable($this->logFilePath)) {
            throw new UnableToWriteToLogFileException(
                sprintf('The log file is not writable: "%s"', $this->logFilePath)
            );
        }
    }

    private function logMessageToFile(LogMessage $message)
    {
        $f = fopen($this->logFilePath, 'a');
        fwrite($f, $this->formatMessage($message));
        fclose($f);
    }

    /**
     * @param LogMessage $message
     * @return string
     */
    private function formatMessage(LogMessage $message)
    {
        $contextStr = preg_replace('/  +/', ' ', str_replace(["\n", "\r"], ' ', print_r($message->getContext(), true)));
        return sprintf("%s\t%s\t%s\n", date('c'), $message, $contextStr);
    }
}
