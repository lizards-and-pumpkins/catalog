<?php

namespace Brera\PoC\Http;

class HttpsUrl extends HttpUrl
{
    /**
     * @return bool
     */
    public function isProtocolEncrypted()
    {
        return true;
    }
} 
