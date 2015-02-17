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
     * @var mixed
     */
    private $content;

    /**
     * @param SearchDocumentFieldCollection $fields
     * @param Environment $environment
     * @param mixed $content
     */
    public function __construct(SearchDocumentFieldCollection $fields, Environment $environment, $content)
    {
        $this->fields = $fields;
        $this->environment = $environment;
        $this->content = $content;
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
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }
}
