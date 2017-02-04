<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins\ProductListing\Import\TemplateRendering;

use LizardsAndPumpkins\Context\DataVersion\DataVersion;
use LizardsAndPumpkins\Import\RootTemplate\TemplateWasUpdatedDomainEvent;

class TemplateProjectionData
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var DataVersion
     */
    private $dataVersion;

    public function __construct(string $content, DataVersion $dataVersion)
    {
        $this->content = $content;
        $this->dataVersion = $dataVersion;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getDataVersion(): DataVersion
    {
        return $this->dataVersion;
    }

    public static function fromEvent(TemplateWasUpdatedDomainEvent $event): self
    {
        return new self($event->getTemplateContent(), $event->getDataVersion());
    }
}
