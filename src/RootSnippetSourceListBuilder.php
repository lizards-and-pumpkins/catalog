<?php

namespace Brera;

use Brera\Context\ContextBuilder;

class RootSnippetSourceListBuilder
{
    /**
     * @var ContextBuilder
     */
    private $contextBuilder;

    public function __construct(ContextBuilder $contextBuilder)
    {
        $this->contextBuilder = $contextBuilder;
    }

    /**
     * @param string $xml
     * @return RootSnippetSource
     */
    public function createFromXml($xml)
    {
        $parser = new XPathParser($xml);

        $sourceNodes = $parser->getXmlNodesArrayByXPath('/listings/products_per_page/number');
        $sourceDataPairs = [];

        foreach ($sourceNodes as $node) {
            $context = $this->contextBuilder->getContext($node['attributes']);
            $numItemsPerPage = $node['value'];

            $sourceDataPairs[] = ['context' => $context, 'numItemsPerPage' => (int) $numItemsPerPage];
        }

        return RootSnippetSourceList::fromArray($sourceDataPairs);
    }
}
