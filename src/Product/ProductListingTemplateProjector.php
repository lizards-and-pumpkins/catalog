<?php

namespace Brera\Product;

use Brera\Context\ContextSource;
use Brera\DataPool\DataPoolWriter;
use Brera\Projector;
use Brera\RootSnippetSourceListBuilder;
use Brera\SnippetRendererCollection;

class ProductListingTemplateProjector implements Projector
{
    /**
     * @var SnippetRendererCollection
     */
    private $snippetRendererCollection;

    /**
     * @var DataPoolWriter
     */
    private $dataPoolWriter;

    /**
     * @var RootSnippetSourceListBuilder
     */
    private $rootSnippetSourceListBuilder;

    public function __construct(
        SnippetRendererCollection $snippetRendererCollection,
        DataPoolWriter $dataPoolWriter,
        RootSnippetSourceListBuilder $rootSnippetSourceListBuilder
    ) {
        $this->snippetRendererCollection = $snippetRendererCollection;
        $this->dataPoolWriter = $dataPoolWriter;
        $this->rootSnippetSourceListBuilder = $rootSnippetSourceListBuilder;
    }

    /**
     * @param mixed $projectionSourceData
     * @param ContextSource $context
     */
    public function project($projectionSourceData, ContextSource $context)
    {
        $rootSnippetSourceList = $this->rootSnippetSourceListBuilder->fromJson($projectionSourceData);
        $snippetList = $this->snippetRendererCollection->render($rootSnippetSourceList, $context);
        $this->dataPoolWriter->writeSnippetList($snippetList);
    }
}
