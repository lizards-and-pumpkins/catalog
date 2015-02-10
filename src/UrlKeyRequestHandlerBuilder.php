<?php


namespace Brera;

use Brera\Environment\Environment;
use Brera\Http\HttpUrl;
use Brera\KeyValue\DataPoolReader;

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

    public function __construct(UrlPathKeyGenerator $urlPathKeyGenerator, DataPoolReader $dataPoolReader)
    {
        $this->urlPathKeyGenerator = $urlPathKeyGenerator;
        $this->dataPoolReader = $dataPoolReader;
    }

    public function create(HttpUrl $url, Environment $environment)
    {
        return new UrlKeyRequestHandler(
            $url,
            $environment,
            $this->urlPathKeyGenerator,
            $this->dataPoolReader
        );
    }
}
