<?php


namespace Brera\PoC;


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

    /**
     * Sends headers, cookies and afterwards the body to the client
     *
     * @return null
     */
    public function send()
    {
        // todo do something!
    }
} 
