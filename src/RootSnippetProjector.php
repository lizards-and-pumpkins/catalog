<?php

namespace Brera;

use Brera\Context\ContextSource;
use Brera\DataPool\DataPoolWriter;

class RootSnippetProjector implements Projector
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
     * @param SnippetRendererCollection $snippetRendererCollection
     * @param DataPoolWriter $dataPoolWriter
     */
    public function __construct(SnippetRendererCollection $snippetRendererCollection, DataPoolWriter $dataPoolWriter)
    {
        $this->snippetRendererCollection = $snippetRendererCollection;
        $this->dataPoolWriter = $dataPoolWriter;
    }

    /**
     * @param ProjectionSourceData $dataObject
     * @param ContextSource $context
     * @throws InvalidProjectionDataSourceTypeException
     */
    public function project(ProjectionSourceData $dataObject, ContextSource $context)
    {
        $snippetResultList = $this->snippetRendererCollection->render($dataObject, $context);
        $this->dataPoolWriter->writeSnippetResultList($snippetResultList);
    }
}
