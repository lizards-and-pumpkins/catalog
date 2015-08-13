<?php

namespace Brera;

use Brera\Context\ContextSource;
use Brera\DataPool\DataPoolWriter;

class TemplateProjector implements Projector
{
    /**
     * @var SnippetRendererCollection
     */
    private $snippetRendererCollection;

    /**
     * @var DataPoolWriter
     */
    private $dataPoolWriter;

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
        if (!($dataObject instanceof RootSnippetSourceList)) {
            throw new InvalidProjectionDataSourceTypeException(
                'First argument must be instance of RootSnippetSourceList.'
            );
        }

        $snippetList = $this->snippetRendererCollection->render($dataObject, $context);
        $this->dataPoolWriter->writeSnippetList($snippetList);
    }
}
