<?php

namespace Brera;

use Brera\Context\Context;
use Brera\Http\HttpUrl;
use Brera\DataPool\DataPoolReader;

class UrlKeyRequestHandlerBuilder
{
    /**
     * @var UrlPathKeyGenerator
     */
    private $urlPathKeyGenerator;

    /**
     * @var DataPoolReader
     */
    private $dataPoolReader;
    
    /**
     * @var SnippetKeyGenerator
     */
    private $keyGenerator;
    
    /**
     * @var Logger
     */
    private $logger;

    public function __construct(
        UrlPathKeyGenerator $urlPathKeyGenerator,
        SnippetKeyGenerator $keyGenerator,
        DataPoolReader $dataPoolReader,
        Logger $logger
    ) {
        $this->urlPathKeyGenerator = $urlPathKeyGenerator;
        $this->dataPoolReader = $dataPoolReader;
        $this->keyGenerator = $keyGenerator;
        $this->logger = $logger;
    }

    public function create(HttpUrl $url, Context $context)
    {
        return new UrlKeyRequestHandler(
            $url,
            $context,
            $this->urlPathKeyGenerator,
            $this->keyGenerator,
            $this->dataPoolReader,
            $this->logger
        );
    }
}
