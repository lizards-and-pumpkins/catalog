<?php

namespace Brera\Content;

use Brera\Context\ContextSource;
use Brera\DataPool\DataPoolWriter;
use Brera\InvalidProjectionSourceDataTypeException;
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
     * @param mixed $projectionSourceData
     * @param ContextSource $contextSource
     * @throws InvalidProjectionSourceDataTypeException
     */
    public function project($projectionSourceData, ContextSource $contextSource)
    {
        if (!($projectionSourceData instanceof ContentBlockSource)) {
            throw new InvalidProjectionSourceDataTypeException(
                'First argument must be instance of ContentBlockSource.'
            );
        }

        $snippetList = $this->snippetRendererCollection->render($projectionSourceData, $contextSource);
        $this->dataPoolWriter->writeSnippetList($snippetList);
    }
}
