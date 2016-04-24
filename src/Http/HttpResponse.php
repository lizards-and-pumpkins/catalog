<?php

namespace LizardsAndPumpkins\Http;

interface HttpResponse
{
    /**
     * @return string
     */
    public function getBody();

    /**
     * @return int
     */
    public function getStatusCode();

    /**
     * @return void
     */
    public function send();
}
