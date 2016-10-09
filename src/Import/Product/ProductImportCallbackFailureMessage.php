<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Logging\LogMessage;
use LizardsAndPumpkins\Import\XPathParser;

class ProductImportCallbackFailureMessage implements LogMessage
{
    private $unknownSku = '- unknown -';

    /**
     * @var \Exception
     */
    private $exception;

    /**
     * @var string
     */
    private $productXml;

    public function __construct(\Exception $exception, string $productXml)
    {
        $this->exception = $exception;
        $this->productXml = $productXml;
    }

    public function __toString() : string
    {
        return sprintf(
            'Error during processing catalog product XML import for product "%s": %s',
            $this->getSkuFromProductXml(),
            $this->exception->getMessage()
        );
    }

    /**
     * @return mixed[]
     */
    public function getContext() : array
    {
        return [
            'exception' => $this->exception,
            'product_xml' => $this->productXml
        ];
    }

    private function getSkuFromProductXml() : string
    {
        try {
            $node = (new XPathParser($this->productXml))->getXmlNodesArrayByXPath('/product/@sku');
        } catch (\Exception $e) {
        }
        return isset($node) && $node ?
            $node[0]['value'] :
            $this->unknownSku;
    }

    public function getContextSynopsis() : string
    {
        return sprintf('File: %s:%d', $this->exception->getFile(), $this->exception->getLine());
    }
}
