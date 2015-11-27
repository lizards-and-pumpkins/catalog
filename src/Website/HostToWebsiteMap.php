<?php


namespace LizardsAndPumpkins\Website;

interface HostToWebsiteMap
{
    /**
     * @param string $host
     * @return string
     */
    public function getWebsiteCodeByHost($host);
}
