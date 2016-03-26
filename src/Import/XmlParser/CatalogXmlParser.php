<?php


namespace LizardsAndPumpkins\Import\XmlParser;

use LizardsAndPumpkins\Import\CatalogListingImportCallbackFailureMessage;
use LizardsAndPumpkins\Import\Product\ProductImportCallbackFailureMessage;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Import\XmlParser\Exception\CatalogImportSourceFilePathIsNotAStringException;
use LizardsAndPumpkins\Import\XmlParser\Exception\CatalogImportSourceXmlFileDoesNotExistException;
use LizardsAndPumpkins\Import\XmlParser\Exception\CatalogImportSourceXmlFileIsNotReadableException;
use LizardsAndPumpkins\Import\XmlParser\Exception\CatalogImportSourceXMLNotAStringException;

class CatalogXmlParser
{
    /**
     * @var \XmlReader
     */
    private $xmlReader;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var callable[]
     */
    private $productCallbacks = [];

    /**
     * @var callable[]
     */
    private $listingCallbacks = [];

    private function __construct(\XMLReader $xmlReader, Logger $logger)
    {
        $this->xmlReader = $xmlReader;
        $this->logger = $logger;
    }

    public function __destruct()
    {
        $this->xmlReader->close();
    }

    /**
     * @param string $sourceFilePath
     * @param Logger $logger
     * @return CatalogXmlParser
     */
    public static function fromFilePath($sourceFilePath, Logger $logger)
    {
        self::validateSourceFilePathIsString($sourceFilePath);
        self::validateSourceFileExists($sourceFilePath);
        self::validateSourceFileIsReadable($sourceFilePath);
        $xmlReader = new \XMLReader();
        $xmlReader->open($sourceFilePath);
        return new self($xmlReader, $logger);
    }

    /**
     * @param string $xmlString
     * @param Logger $logger
     * @return CatalogXmlParser
     */
    public static function fromXml($xmlString, Logger $logger)
    {
        self::validateSourceXmlIsString($xmlString);
        $xmlReader = new \XMLReader();
        $xmlReader->XML($xmlString);
        return new self($xmlReader, $logger);
    }

    /**
     * @param string $sourceFilePath
     */
    private static function validateSourceFilePathIsString($sourceFilePath)
    {
        if (!is_string($sourceFilePath)) {
            throw new CatalogImportSourceFilePathIsNotAStringException(sprintf(
                'Expected the catalog XML import file path to be a string, got "%s"',
                self::getVariableType($sourceFilePath)
            ));
        }
    }

    /**
     * @param string $sourceFilePath
     */
    private static function validateSourceFileExists($sourceFilePath)
    {
        if (!file_exists($sourceFilePath)) {
            throw new CatalogImportSourceXmlFileDoesNotExistException(
                sprintf('The catalog XML import file "%s" does not exist', $sourceFilePath)
            );
        }
    }

    /**
     * @param string $sourceFilePath
     */
    private static function validateSourceFileIsReadable($sourceFilePath)
    {
        if (!is_readable($sourceFilePath)) {
            throw new CatalogImportSourceXmlFileIsNotReadableException(
                sprintf('The catalog XML import file "%s" is not readable', $sourceFilePath)
            );
        }
    }

    /**
     * @param string $xmlString
     */
    private static function validateSourceXmlIsString($xmlString)
    {
        if (!is_string($xmlString)) {
            throw new CatalogImportSourceXMLNotAStringException(sprintf(
                'Expected the catalog XML to be a string, got "%s"',
                self::getVariableType($xmlString)
            ));
        }
    }

    /**
     * @param mixed $variable
     * @return string
     */
    private static function getVariableType($variable)
    {
        return is_object($variable) ?
            get_class($variable) :
            gettype($variable);
    }

    public function registerProductCallback(callable $callback)
    {
        $this->productCallbacks[] = $callback;
    }

    public function registerListingCallback(callable $callback)
    {
        $this->listingCallbacks[] = $callback;
    }

    public function parse()
    {
        while ($this->xmlReader->read()) {
            if ($this->isProductNode()) {
                $this->parseProductXml();
            } elseif ($this->isListingNode()) {
                $this->parseListingXml();
            }
        }
    }

    private function parseProductXml()
    {
        $productXml = $this->xmlReader->readOuterXml();
        try {
            $this->processCallbacksWithArg($this->productCallbacks, $productXml);
        } catch (\Exception $exception) {
            $this->logger->log(new ProductImportCallbackFailureMessage($exception, $productXml));
        }
    }

    private function parseListingXml()
    {
        $listingXml = $this->xmlReader->readOuterXml();
        try {
            $this->processCallbacksWithArg($this->listingCallbacks, $listingXml);
        } catch (\Exception $exception) {
            $this->logger->log(new CatalogListingImportCallbackFailureMessage($exception, $listingXml));
        }
    }
    
    /**
     * @return bool
     */
    private function isProductNode()
    {
        return $this->isElementOnDepth('product', 2);
    }

    /**
     * @return bool
     */
    private function isListingNode()
    {
        return $this->isElementOnDepth('listing', 2);
    }

    /**
     * @param string $name
     * @param int $depth
     * @return bool
     */
    private function isElementOnDepth($name, $depth)
    {
        return
            $this->xmlReader->nodeType === \XMLReader::ELEMENT &&
            $this->xmlReader->name === $name &&
            $this->xmlReader->depth === $depth;
    }

    /**
     * @param callable[] $callbacks
     * @param string $argument
     */
    private function processCallbacksWithArg(array $callbacks, $argument)
    {
        @array_map(function (callable $callback) use ($argument) {
            call_user_func($callback, $argument);
        }, $callbacks);
    }
}
