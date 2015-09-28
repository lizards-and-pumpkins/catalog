<?php


namespace LizardsAndPumpkins\Projection\Catalog\Import;

use LizardsAndPumpkins\Projection\Catalog\Import\Exception\CatalogImportSourceFilePathIsNotAStringException;
use LizardsAndPumpkins\Projection\Catalog\Import\Exception\CatalogImportSourceXmlFileDoesNotExistException;
use LizardsAndPumpkins\Projection\Catalog\Import\Exception\CatalogImportSourceXmlFileIsNotReadableException;
use LizardsAndPumpkins\Projection\Catalog\Import\Exception\CatalogImportSourceXMLNotAStringException;
use LizardsAndPumpkins\Utils\XPathParser;

class CatalogXmlParser
{
    /**
     * @var \XmlReader
     */
    private $xmlReader;

    /**
     * @var callable[]
     */
    private $productCallbacks = [];

    /**
     * @var callable[]
     */
    private $listingCallbacks = [];

    /**
     * @var callable[]
     */
    private $productImageCallbacks = [];

    private function __construct(\XMLReader $xmlReader)
    {
        $this->xmlReader = $xmlReader;
    }

    public function __destruct()
    {
        $this->xmlReader->close();
    }

    /**
     * @param string $sourceFilePath
     * @return CatalogXmlParser
     */
    public static function fromFilePath($sourceFilePath)
    {
        self::validateSourceFilePathIsString($sourceFilePath);
        self::validateSourceFileExists($sourceFilePath);
        self::validateSourceFileIsReadable($sourceFilePath);
        $xmlReader = new \XMLReader();
        $xmlReader->open($sourceFilePath);
        return new self($xmlReader);
    }

    /**
     * @param string $xmlString
     * @return CatalogXmlParser
     */
    public static function fromXml($xmlString)
    {
        self::validateSourceXmlIsString($xmlString);
        $xmlReader = new \XMLReader();
        $xmlReader->xml($xmlString);
        return new self($xmlReader);
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

    public function registerProductImageCallback(callable $callback)
    {
        $this->productImageCallbacks[] = $callback;
    }

    public function parse()
    {
        while ($this->xmlReader->read()) {
            if ($this->isProductNode()) {
                $productXml = $this->xmlReader->readOuterXml();
                $this->processCallbacksWithArg($this->productCallbacks, $productXml);
                $this->processImageCallbacksForProductXml($productXml);
            } elseif ($this->isListingNode()) {
                $this->processCallbacksWithCurrentNode($this->listingCallbacks);
            }
        }
    }

    /**
     * @param string $productXml
     */
    private function processImageCallbacksForProductXml($productXml)
    {
        $imageNodes = (new XPathParser($productXml))->getXmlNodesRawXmlArrayByXPath('/product/images/image');
        array_map(function ($imageXml) {
            $this->processCallbacksWithArg($this->productImageCallbacks, $imageXml);
        }, $imageNodes);
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
     */
    private function processCallbacksWithCurrentNode(array $callbacks)
    {
        $xmlString = $this->xmlReader->readOuterXml();
        $this->processCallbacksWithArg($callbacks, $xmlString);
    }

    /**
     * @param callable[] $callbacks
     * @param string $argument
     */
    private function processCallbacksWithArg(array $callbacks, $argument)
    {
        array_map(function (callable $callback) use ($argument) {
            call_user_func($callback, $argument);
        }, $callbacks);
    }
}
