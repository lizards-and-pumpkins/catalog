<?php


namespace Brera;

use Brera\Context\Context;
use Brera\Http\HttpUrl;
use Brera\DataPool\DataPoolReader;
use Psr\Log\LoggerInterface;

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
     * @var SnippetKeyGeneratorLocator
     */
    private $keyGeneratorLocator;
    
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        UrlPathKeyGenerator $urlPathKeyGenerator,
        SnippetKeyGeneratorLocator $keyGeneratorLocator,
        DataPoolReader $dataPoolReader,
        LoggerInterface $logger
    ) {
        $this->urlPathKeyGenerator = $urlPathKeyGenerator;
        $this->dataPoolReader = $dataPoolReader;
        $this->keyGeneratorLocator = $keyGeneratorLocator;
        $this->logger = $logger;
    }

    public function create(HttpUrl $url, Context $context)
    {
        return new UrlKeyRequestHandler(
            $url,
            $context,
            $this->urlPathKeyGenerator,
            $this->keyGeneratorLocator,
            $this->dataPoolReader,
            $this->logger
        );
    }
}
