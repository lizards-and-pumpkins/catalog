<?php


namespace LizardsAndPumpkins\Log\Writer;

use LizardsAndPumpkins\Log\LogMessage;

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
        $contextStr = $this->formatContextString($message->getContext());
        return sprintf("%s\t%s\t%s\t%s\n", date('c'), $message, get_class($message), $contextStr);
    }

    /**
     * @param mixed[] $contextData
     * @return string
     */
    private function formatContextString(array $contextData)
    {
        $contextInfo = array_map(function ($name) use ($contextData) {
            return sprintf('%s => %s', $name, $this->formatContextInfoElement($contextData[$name]));
        }, array_keys($contextData));
        return preg_replace('/  +/', ' ', str_replace(["\n", "\r"], ' ', print_r($contextInfo, true)));
    }

    /**
     * @param mixed $data
     * @return string
     */
    private function formatContextInfoElement($data)
    {
        switch (gettype($data)) {
            case 'object':
                $value = $this->getObjectInfoString($data);
                break;
            case 'array':
                $value = $this->getArrayInfoString($data);
                break;
            case 'string':
            case 'integer':
            case 'float':
                $value = $this->getScalarInfoString($data);
                break;
            default:
                $value = gettype($data);
                break;
        }
        return $value;
    }

    /**
     * @param object $object
     * @return string
     */
    private function getObjectInfoString($object)
    {
        if ($object instanceof \Exception) {
            $info = get_class($object) . ' ' . $object->getFile() . ':' . $object->getLine();
        } else {
            $info = get_class($object);
        }
        return $info;
    }

    /**
     * @param mixed[] $array
     * @return string
     */
    private function getArrayInfoString(array $array)
    {
        return 'Array(' . count($array) . ')';
    }

    /**
     * @param string|int|float $data
     * @return string
     */
    private function getScalarInfoString($data)
    {
        return sprintf('(%s) %s', gettype($data), $data);
    }
}
