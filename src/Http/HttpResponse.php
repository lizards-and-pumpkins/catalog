<?php

namespace Brera\Http;

interface HttpResponse
{
    /**
     * @return string
     */
    public function getBody();

    /**
     * Sends headers, cookies and afterwards the body to the client
     *
     * @return null
     */
    public function send();
}
