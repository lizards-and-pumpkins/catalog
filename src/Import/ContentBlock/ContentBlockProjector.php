<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\ContentBlock;

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
     * @param mixed $projectionSourceData
     */
    public function project($projectionSourceData)
    {
        $this->projectSnippets($projectionSourceData);
    }

    private function projectSnippets(ContentBlockSource $projectionData)
    {
        $this->snippetProjector->project($projectionData);
    }
}
