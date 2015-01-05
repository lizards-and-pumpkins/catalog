<?php

namespace Brera\Http;

interface HttpResponse
{
    /**
     * @return string
     */
    public function getBody();

    /**
     * @param string $content
     * @return null
     */
    public function setBody($content);

    /**
     * Sends headers, cookies and afterwards the body to the client
     * 
     * @return null
     */
    public function send();
} 
