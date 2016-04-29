<?php

namespace LizardsAndPumpkins\Context\Website;

interface UrlToWebsiteMap
{
    /**
     * @param string $host
     * @return Website
     */
    public function getWebsiteCodeByHost($host);
}
