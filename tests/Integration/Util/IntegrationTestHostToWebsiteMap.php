<?php


namespace LizardsAndPumpkins;

use LizardsAndPumpkins\Website\HostToWebsiteMap;

class IntegrationTestHostToWebsiteMap implements HostToWebsiteMap
{
    /**
     * @param string $code
     * @return string
     */
    public function getWebsiteCodeByHost($code)
    {
        return 'ru';
    }
}
