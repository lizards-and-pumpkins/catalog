<?php
namespace Brera\PoC;

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
