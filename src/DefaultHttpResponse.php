<?php

namespace Brera;

use Brera\Http\HttpResponse;

class DefaultHttpResponse implements HttpResponse
{
    /**
     * @var string
     */
    private $body;

    /**
     * @param string $content
     * @return null
     */
    public function setBody($content)
    {
        $this->body = $content;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    public function send()
    {
        echo $this->getBody();
    }
}
