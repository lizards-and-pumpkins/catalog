<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ContentBlock;

use LizardsAndPumpkins\Import\Exception\InvalidProjectionSourceDataTypeException;
use LizardsAndPumpkins\Import\Projector;

class ContentBlockProjector implements Projector
{
    /**
     * @var Projector
     */
    private $snippetProjector;

    public function __construct(Projector $snippetProjector)
    {
        $this->snippetProjector = $snippetProjector;
    }

    /**
     * @param ContentBlockSource $contentBlockSource
     */
    public function project($contentBlockSource): void
    {
        if (! $contentBlockSource instanceof ContentBlockSource) {
            throw new InvalidProjectionSourceDataTypeException(sprintf(
                'Projection source data must be of ContentBlockSource type, got "%s".',
                typeof($contentBlockSource)
            ));
        }

        $this->snippetProjector->project($contentBlockSource);
    }
}
