<?php

namespace Brera;

class RootTemplateChangedDomainEvent implements DomainEvent
{
    /**
     * @var string
     */
    private $layoutHandle;

    /**
     * @param string $layoutHandle
     */
    public function __construct($layoutHandle)
    {
        $this->layoutHandle = $layoutHandle;
    }

    /**
     * @return string
     */
    public function getLayoutHandle()
    {
        return $this->layoutHandle;
    }
}
