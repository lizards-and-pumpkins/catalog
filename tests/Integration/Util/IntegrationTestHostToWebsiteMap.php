<?php


namespace LizardsAndPumpkins;

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
