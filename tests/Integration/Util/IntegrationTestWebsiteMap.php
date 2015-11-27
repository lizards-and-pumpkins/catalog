<?php


namespace LizardsAndPumpkins;

class IntegrationTestWebsiteMap extends WebsiteMap
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
