<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http;

use LizardsAndPumpkins\Context\Website\UrlToWebsiteMap;

class UrlToWebsiteMapBasedUrlParser implements HttpUrlParser
{
    /**
     * @var UrlToWebsiteMap
     */
    private $urlToWebsiteMap;

    public function __construct(UrlToWebsiteMap $urlToWebsiteMap)
    {
        $this->urlToWebsiteMap = $urlToWebsiteMap;
    }

    public function getPath(HttpUrl $url): string
    {
        return $this->urlToWebsiteMap->getRequestPathWithoutWebsitePrefix((string) $url);
    }
}
