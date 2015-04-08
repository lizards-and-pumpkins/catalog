<?php


namespace Brera\Context;

use Brera\DataVersion;

class InternalContextState implements ContextState
{
    /**
     * @var string
     */
    private $version;
    
    /**
     * @var string[]
     */
    private $contextDataSet;

    /**
     * @param string $version
     * @param string[] $contextDataSet
     */
    private function __construct($version, array $contextDataSet)
    {
        $this->version = $version;
        $this->contextDataSet = $contextDataSet;
    }

    /**
     * @param DataVersion $dataVersion
     * @param string[] $contextDataSet
     * @return ContextState
     */
    public static function fromContextFields(DataVersion $dataVersion, array $contextDataSet)
    {
        return new self((string) $dataVersion, $contextDataSet);
    }

    /**
     * @param string $serializedStateString
     * @return ContextState
     */
    public static function fromStringRepresentation($serializedStateString)
    {
        if (!is_string($serializedStateString)) {
            throw new InvalidContextStateRepresentationException(
                'The context state representation has to be specified as a string'
            );
        }
        $data = self::decodeStringRepresentation($serializedStateString);
        $version = self::getVersionFromDecodedData($data);
        $dataSet = self::getContextSetFromDecodedData($data);
        return new self($version, $dataSet);
    }

    /**
     * @param string $serializedStateString
     * @return mixed[]
     * @throws InvalidContextStateRepresentationException
     */
    private static function decodeStringRepresentation($serializedStateString)
    {
        $data = json_decode($serializedStateString, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidContextStateRepresentationException(
                'The context state representation could not be decoded from JSON'
            );
        }
        return $data;
    }

    /**
     * @param mixed[] $data
     * @return string
     */
    private static function getVersionFromDecodedData(array $data)
    {
        if (!array_key_exists('data_version', $data)) {
            throw new InvalidContextStateRepresentationException(
                'Missing data version on context string representation'
            );
        }
        return (string)$data['data_version'];
    }

    /**
     * @param mixed[] $data
     * @return string[]
     */
    private static function getContextSetFromDecodedData(array $data)
    {
        if (!array_key_exists('context_set', $data) || !is_array($data['context_set'])) {
            throw new InvalidContextStateRepresentationException(
                'Missing context data set on context string representation'
            );
        }
        return $data['context_set'];
    }

    /**
     * @return string[]
     */
    public function getContextDataSet()
    {
        return $this->contextDataSet;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getStringRepresentation()
    {
        return json_encode([
            'data_version' => (string) $this->version,
            'context_set' => $this->contextDataSet
        ]);
    }
}
