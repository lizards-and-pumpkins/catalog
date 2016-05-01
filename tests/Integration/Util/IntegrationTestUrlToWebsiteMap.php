<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Website\UrlToWebsiteMap;

class IntegrationTestUrlToWebsiteMap implements UrlToWebsiteMap
{
    /**
     * @param string $url
     * @return string
     */
    public function getWebsiteCodeByUrl($url)
    {
        return 'fr';
    }
}
