<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Import\FileStorage\Stub;

class CastableToStringStub
{
    public function __toString() : string
    {
        return 'stub string content';
    }
}
