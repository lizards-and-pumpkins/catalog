<?php
namespace Brera;

interface SnippetRenderer
{
    /**
     * @param ProjectionSourceData $dataObject
     * @param Environment $environment
     *
     * @return SnippetResultList
     */
    public function render(ProjectionSourceData $dataObject, Environment $environment);
}
