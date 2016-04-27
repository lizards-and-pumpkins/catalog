<?php

namespace LizardsAndPumpkins\Http;

interface HttpResponse
{
    const STATUS_OK = 200;
    const STATUS_ACCEPTED = 202;
    const STATUS_NOT_FOUND = 404;

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
