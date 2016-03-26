<?php


namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Context\Website\HostToWebsiteMap;

class IntegrationTestHostToWebsiteMap implements HostToWebsiteMap
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
