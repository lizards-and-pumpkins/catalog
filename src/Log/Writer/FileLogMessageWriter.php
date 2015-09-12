<?php


namespace Brera\Log\Writer;

use Brera\Log\LogMessage;

class FileLogMessageWriter implements LogMessageWriter
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

    /**
     * @param string $messageString
     */
    private function writeToFile($messageString)
    {
        $f = fopen($this->logFilePath, 'a');
        flock($f, LOCK_EX);
        fwrite($f, $messageString);
        flock($f, LOCK_UN);
        fclose($f);
    }

    /**
     * @param LogMessage $message
     * @return string
     */
    private function formatMessage(LogMessage $message)
    {
        $contextStr = $this->formatContextString($message);
        return sprintf("%s\t%s\t%s\t%s\n", date('c'), $message, get_class($message), $contextStr);
    }

    /**
     * @param LogMessage $message
     * @return string
     */
    private function formatContextString(LogMessage $message)
    {
        // Todo: truncate large context data
        $contextData = $message->getContext();
        $data = array_map(function ($name) use ($contextData) {
            switch (gettype($contextData[$name])) {
                case 'string':
                    $value = $contextData[$name];
                    break;
                case 'object':
                    $value = get_class($contextData[$name]);
                    break;
                case 'array':
                    $value = 'Array(' . count($contextData[$name]) . ')';
                    break;
                default:
                    $value = gettype($contextData[$name]);
                    break;
            }
            return sprintf('%s => %s', $name, $value);
        }, array_keys($contextData));
        return preg_replace('/  +/', ' ', str_replace(["\n", "\r"], ' ', print_r($data, true)));
    }
}
