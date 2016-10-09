<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Logging\Writer;

use LizardsAndPumpkins\Logging\LogMessage;
use LizardsAndPumpkins\Logging\LogMessageWriter;
use LizardsAndPumpkins\Logging\Writer\Exception\UnableToCreateLogDirectoryException;
use LizardsAndPumpkins\Logging\Writer\Exception\UnableToCreateLogFileException;
use LizardsAndPumpkins\Logging\Writer\Exception\UnableToWriteToLogFileException;

class FileLogMessageWriter implements LogMessageWriter
{
    /**
     * @var string
     */
    private $logFilePath;

    public function __construct(string $logFilePath)
    {
        $this->logFilePath = $logFilePath;
    }

    public function write(LogMessage $logMessage)
    {
        $this->createLogDirIfNotExists();
        $this->validateLogFileIsWritable();

        $this->writeToFile($this->formatMessage($logMessage));
    }

    private function createLogDirIfNotExists()
    {
        $logDirPath = dirname($this->logFilePath);
        if (!file_exists($logDirPath)) {
            $this->createLogDir($logDirPath);
        }
    }

    private function createLogDir(string $logDirPath)
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

    private function writeToFile(string $messageString)
    {
        $f = fopen($this->logFilePath, 'a');
        flock($f, LOCK_EX);
        fwrite($f, $messageString);
        flock($f, LOCK_UN);
        fclose($f);
    }

    private function formatMessage(LogMessage $message) : string
    {
        return sprintf("%s\t%s\t%s\t%s\n", date('c'), $message, get_class($message), $message->getContextSynopsis());
    }
}
