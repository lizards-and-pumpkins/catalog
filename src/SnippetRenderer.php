<?php
namespace Brera;

use Brera\Context\ContextSource;

interface SnippetRenderer
{
    /**
     * @param ProjectionSourceData $dataObject
     * @param ContextSource $contextSource
     * @return SnippetResultList
     */
    public function render(ProjectionSourceData $dataObject, ContextSource $contextSource);
}
