<?php


namespace LizardsAndPumpkins;

interface HostToWebsiteMap
{
    /**
     * @param string $host
     * @return string
     */
    public function getWebsiteCodeByHost($host);
}
