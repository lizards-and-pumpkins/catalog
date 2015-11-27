<?php


namespace LizardsAndPumpkins;

class IntegrationTestHostToWebsiteMap extends HostToWebsiteMap
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
