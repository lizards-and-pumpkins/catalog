<?php


namespace LizardsAndPumpkins;

class IntegrationTestWebsiteMap extends WebsiteMap
{
    /**
     * @param string $code
     * @return string
     */
    public function getCodeByHost($code)
    {
        return 'ru';
    }
}
