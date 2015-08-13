<?php

namespace Brera;

class TemplateWasUpdatedDomainEvent implements DomainEvent
{
    /**
     * @var string
     */
    private $templateId;

    /**
     * @var ProjectionSourceData
     */
    private $projectionSourceData;

    /**
     * @param string $templateId
     * @param ProjectionSourceData $projectionSourceData
     */
    public function __construct($templateId, ProjectionSourceData $projectionSourceData)
    {
        $this->templateId = $templateId;
        $this->projectionSourceData = $projectionSourceData;
    }

    /**
     * @return ProjectionSourceData
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
