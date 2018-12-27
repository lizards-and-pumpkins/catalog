<?php
declare(strict_types=1);

namespace LizardsAndPumpkins\Import\TemplateRendering;

class TemplateFile implements Template
{
    /**
     * @var string
     */
    private $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function __toString(): string
    {
        return $this->getPath();
    }
}
