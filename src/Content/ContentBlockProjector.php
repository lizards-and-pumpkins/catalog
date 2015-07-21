<?php

namespace Brera\Content;

use Brera\Context\ContextSource;
use Brera\DataPool\DataPoolWriter;
use Brera\InvalidProjectionDataSourceTypeException;
use Brera\ProjectionSourceData;
use Brera\Projector;
use Brera\SnippetRendererCollection;

class ContentBlockProjector implements Projector
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
     * @param ProjectionSourceData $projectionSourceData
     * @param ContextSource $contextSource
     * @throws InvalidProjectionDataSourceTypeException
     */
    public function project(ProjectionSourceData $projectionSourceData, ContextSource $contextSource)
    {
        if (!($projectionSourceData instanceof ContentBlockSource)) {
            throw new InvalidProjectionDataSourceTypeException(
                'First argument must be instance of ContentBlockSource.'
            );
        }

        $snippetList = $this->snippetRendererCollection->render($projectionSourceData, $contextSource);
        $this->dataPoolWriter->writeSnippetList($snippetList);
    }
}
