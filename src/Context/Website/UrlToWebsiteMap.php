<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Context\Website;

interface UrlToWebsiteMap
{
    public function getWebsiteCodeByUrl(string $url): Website;
    
    public function getRequestPathWithoutWebsitePrefix(string $url): string;
}
