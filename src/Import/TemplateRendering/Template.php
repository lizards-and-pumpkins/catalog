<?php
declare(strict_types=1);

namespace LizardsAndPumpkins\Import\TemplateRendering;

interface Template
{
    public function getPath(): string;

    public function __toString(): string;
}
