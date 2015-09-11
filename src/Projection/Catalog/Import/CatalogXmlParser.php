<?php


namespace Brera\Projection\Catalog\Import;

use Brera\Product\ProductAttributeList;
use Brera\Product\ProductId;
use Brera\Product\ProductSource;
use Brera\Product\SampleSku;

class CatalogXmlParser
{
    /**
     * @var \XmlReader
     */
    private $xmlReader;

    /**
     * @var callable[]
     */
    private $productSourceCallbacks = [];

    /**
     * @var callable[]
     */
    private $listingCallbacks = [];

    private function __construct(\XmlReader $xmlReader)
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
            throw new Exception\CatalogImportSourceFilePathIsNotAStringException(sprintf(
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
            throw new Exception\CatalogImportSourceXmlFileDoesNotExistException(
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
            throw new Exception\CatalogImportSourceXmlFileIsNotReadableException(
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
            throw new Exception\CatalogImportSourceXMLNotAStringException(sprintf(
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

    public function parse()
    {
        while ($this->xmlReader->read()) {
            if ($this->isProductNode()) {
                $this->processElementCallbacks($this->productSourceCallbacks);
            } elseif ($this->isListingNode()) {
                $this->processElementCallbacks($this->listingCallbacks);
            }
        }
    }

    public function registerProductSourceCallback(callable $callback)
    {
        $this->productSourceCallbacks[] = $callback;
    }

    public function registerListingCallback(callable $callback)
    {
        $this->listingCallbacks[] = $callback;
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
    private function processElementCallbacks($callbacks)
    {
        $xmlString = $this->xmlReader->readOuterXml();
        array_map(function (callable $callback) use ($xmlString) {
            call_user_func($callback, $xmlString);
        }, $callbacks);
        $this->xmlReader->next();
    }
}
