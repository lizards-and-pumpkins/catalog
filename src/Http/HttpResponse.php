<?php

namespace LizardsAndPumpkins\Http;

interface HttpResponse
{
    /**
     * @return string
     */
    public function getBody();

    /**
     * @return void
     */
    public function send();
}
