<?php


namespace Brera\Projection\Catalog\Import;

use Brera\Product\ProductAttributeList;
use Brera\Product\ProductId;
use Brera\Product\ProductSource;
use Brera\Product\SampleSku;

class CatalogXmlParser
{
    /**
     * @var callable[]
     */
    private $productSourceCreatedCallback = [];

    /**
     * @param string $sourceFilePath
     * @return CatalogXmlParser
     */
    public static function forFile($sourceFilePath)
    {
        if (!is_string($sourceFilePath)) {
            throw new Exception\CatalogImportSourceFilePathIsNotAStringException(sprintf(
                'Expected the catalog XML import file path to be a string, got "%s"',
                self::getVariableType($sourceFilePath)
            ));
        }
        if (!file_exists($sourceFilePath)) {
            throw new Exception\CatalogImportSourceXmlFileDoesNotExistException(
                sprintf('The catalog XML import file "%s" does not exist', $sourceFilePath)
            );
        }
        if (!is_readable($sourceFilePath)) {
            throw new Exception\CatalogImportSourceXmlFileIsNotReadableException(
                sprintf('The catalog XML import file "%s" is not readable', $sourceFilePath)
            );
        }
        return new self($sourceFilePath);
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

    public function registerCallbackForProductSource(callable $callback)
    {
        $this->productSourceCreatedCallback[] = $callback;
    }

    public function parse()
    {
        $sku = SampleSku::fromString('test');
        $productSource = new ProductSource(ProductId::fromSku($sku), ProductAttributeList::fromArray([]));
        $this->notifyProductSourceCallbacks($productSource);
    }

    private function notifyProductSourceCallbacks(ProductSource $productSource)
    {
        array_map(function (callable $f) use ($productSource) {
            $f($productSource);
        }, $this->productSourceCreatedCallback);
    }


}
