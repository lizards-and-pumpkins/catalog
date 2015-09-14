<?php

namespace LizardsAndPumpkins;

class TemplateWasUpdatedDomainEvent implements DomainEvent
{
    /**
     * @var string
     */
    private $templateId;

    /**
     * @var mixed
     */
    private $projectionSourceData;

    /**
     * @param string $templateId
     * @param mixed $projectionSourceData
     */
    public function __construct($templateId, $projectionSourceData)
    {
        $this->templateId = $templateId;
        $this->projectionSourceData = $projectionSourceData;
    }

    /**
     * @return mixed
     */
    public function getProjectionSourceData()
    {
        return $this->projectionSourceData;
    }

    /**
     * @return string
     */
    public function getTemplateId()
    {
        return $this->templateId;
    }
}
