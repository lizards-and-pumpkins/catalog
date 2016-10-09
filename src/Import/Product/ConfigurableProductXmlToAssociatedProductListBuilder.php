<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\Product;

use LizardsAndPumpkins\Import\Product\Composite\AssociatedProductListBuilder;
use LizardsAndPumpkins\Import\XPathParser;

class ConfigurableProductXmlToAssociatedProductListBuilder
{
    /**
     * @var ProductXmlToProductBuilderLocator
     */
    private $productXmlToProductBuilderLocator;

    public function __construct(ProductXmlToProductBuilderLocator $productXmlToProductBuilderLocator)
    {
        $this->productXmlToProductBuilderLocator = $productXmlToProductBuilderLocator;
    }

    public function createAssociatedProductListBuilder(XPathParser $parser) : AssociatedProductListBuilder
    {
        $productBuilders = array_map(function ($associatedProductXml) {
            return $this->productXmlToProductBuilderLocator->createProductBuilderFromXml($associatedProductXml);
        }, $parser->getXmlNodesRawXmlArrayByXPath('/product/associated_products/product'));
        return new AssociatedProductListBuilder(...$productBuilders);
    }
}
