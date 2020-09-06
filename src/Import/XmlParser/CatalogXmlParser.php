<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\XmlParser;

use LizardsAndPumpkins\Import\CatalogListingImportCallbackFailureMessage;
use LizardsAndPumpkins\Import\Product\ProductImportCallbackFailureMessage;
use LizardsAndPumpkins\Logging\Logger;
use LizardsAndPumpkins\Import\XmlParser\Exception\CatalogImportSourceXmlFileDoesNotExistException;
use LizardsAndPumpkins\Import\XmlParser\Exception\CatalogImportSourceXmlFileIsNotReadableException;

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

    public static function fromFilePath(string $sourceFilePath, Logger $logger) : CatalogXmlParser
    {
        self::validateSourceFileExists($sourceFilePath);
        self::validateSourceFileIsReadable($sourceFilePath);
        $xmlReader = new \XMLReader();
        $xmlReader->open($sourceFilePath);
        return new self($xmlReader, $logger);
    }

    public static function fromXml(string $xmlString, Logger $logger) : CatalogXmlParser
    {
        $xmlReader = new \XMLReader();
        $xmlReader->XML($xmlString);
        return new self($xmlReader, $logger);
    }

    private static function validateSourceFileExists(string $sourceFilePath): void
    {
        if (!file_exists($sourceFilePath)) {
            throw new CatalogImportSourceXmlFileDoesNotExistException(
                sprintf('The catalog XML import file "%s" does not exist', $sourceFilePath)
            );
        }
    }

    private static function validateSourceFileIsReadable(string $sourceFilePath): void
    {
        if (!is_readable($sourceFilePath)) {
            throw new CatalogImportSourceXmlFileIsNotReadableException(
                sprintf('The catalog XML import file "%s" is not readable', $sourceFilePath)
            );
        }
    }

    public function registerProductCallback(callable $callback): void
    {
        $this->productCallbacks[] = $callback;
    }

    public function registerListingCallback(callable $callback): void
    {
        $this->listingCallbacks[] = $callback;
    }

    public function parse(): void
    {
        while ($this->xmlReader->read()) {
            if ($this->isProductNode()) {
                $this->parseProductXml();
            } elseif ($this->isListingNode()) {
                $this->parseListingXml();
            }
        }
    }

    private function parseProductXml(): void
    {
        $productXml = $this->xmlReader->readOuterXml();
        try {
            $this->processCallbacksWithArg($this->productCallbacks, $productXml);
        } catch (\Exception $exception) {
            $this->logger->log(new ProductImportCallbackFailureMessage($exception, $productXml));
        }
    }

    private function parseListingXml(): void
    {
        $listingXml = $this->xmlReader->readOuterXml();
        try {
            $this->processCallbacksWithArg($this->listingCallbacks, $listingXml);
        } catch (\Exception $exception) {
            $this->logger->log(new CatalogListingImportCallbackFailureMessage($exception, $listingXml));
        }
    }
    
    private function isProductNode() : bool
    {
        return $this->isElementOnDepth('product', 2);
    }

    private function isListingNode() : bool
    {
        return $this->isElementOnDepth('listing', 2);
    }

    private function isElementOnDepth(string $name, int $depth) : bool
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
    private function processCallbacksWithArg(array $callbacks, string $argument): void
    {
        array_map(function (callable $callback) use ($argument) {
            call_user_func($callback, $argument);
        }, $callbacks);
    }
}
