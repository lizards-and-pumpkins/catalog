<?php

namespace Brera\KeyValue;

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
    private $payload;

    /**
     * @param SearchDocumentFieldCollection $fields
     * @param Environment $environment
     * @param mixed $payload
     */
    public function __construct(SearchDocumentFieldCollection $fields, Environment $environment, $payload)
    {
        $this->fields = $fields;
        $this->environment = $environment;
        $this->payload = $payload;
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
    public function getPayload()
    {
        return $this->payload;
    }
}
