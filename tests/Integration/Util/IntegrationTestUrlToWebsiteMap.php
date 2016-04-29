<?php

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Website\UrlToWebsiteMap;

class IntegrationTestUrlToWebsiteMap implements UrlToWebsiteMap
{
    /**
     * @param string $host
     * @return string
     */
    public function getWebsiteCodeByHost($host)
    {
        return 'fr';
    }
}
