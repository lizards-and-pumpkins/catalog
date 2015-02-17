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

    public function __construct(UrlPathKeyGenerator $urlPathKeyGenerator, DataPoolReader $dataPoolReader)
    {
        $this->urlPathKeyGenerator = $urlPathKeyGenerator;
        $this->dataPoolReader = $dataPoolReader;
    }

    public function create(HttpUrl $url, Context $context)
    {
        return new UrlKeyRequestHandler(
            $url,
            $context,
            $this->urlPathKeyGenerator,
            $this->dataPoolReader
        );
    }
}
