<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\DataPool;

interface CurrentDataVersion
{
    const SNIPPET_KEY = 'current_version';
    const PREVIOUS_VERSION_SNIPPET_KEY = 'previous_version';
}
