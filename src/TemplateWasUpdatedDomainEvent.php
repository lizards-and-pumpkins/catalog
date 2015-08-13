<?php

namespace Brera;

class TemplateWasUpdatedDomainEvent implements DomainEvent
{
    /**
     * @var string
     */
    private $rootSnippetId;

    /**
     * @var RootSnippetSourceList
     */
    private $rootSnippetSourceList;

    /**
     * @param string $rootSnippetId
     * @param RootSnippetSourceList $rootSnippetSourceList
     */
    public function __construct($rootSnippetId, RootSnippetSourceList $rootSnippetSourceList)
    {
        $this->rootSnippetId = $rootSnippetId;
        $this->rootSnippetSourceList = $rootSnippetSourceList;
    }

    /**
     * @return RootSnippetSourceList
     */
    public function getProjectionSourceData()
    {
        return $this->rootSnippetSourceList;
    }
}
