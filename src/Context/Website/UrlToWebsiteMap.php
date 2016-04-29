<?php

namespace LizardsAndPumpkins\Context\Website;

interface UrlToWebsiteMap
{
    /**
     * @param string $url
     * @return Website
     */
    public function getWebsiteCodeByUrl($url);
}
