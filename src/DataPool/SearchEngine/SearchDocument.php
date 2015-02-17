<?php

namespace Brera\DataPool\SearchEngine;

use Brera\Environment\Environment;

class SearchDocument
{
    /**
     * @var SearchDocumentFieldCollection
     */
    private $fields;

    /**
     * @var Environment
     */
    private $environment;

    /**
     * @var string
     */
    private $content;

    /**
     * @param SearchDocumentFieldCollection $fields
     * @param Environment $environment
     * @param string $content
     */
    public function __construct(SearchDocumentFieldCollection $fields, Environment $environment, $content)
    {
        $this->fields = $fields;
        $this->environment = $environment;
        $this->content = (string) $content;
    }

    /**
     * @return SearchDocumentFieldCollection
     */
    public function getFieldsCollection()
    {
        return $this->fields;
    }

    /**
     * @return Environment
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}
