<?php

namespace Brera\PoC\Http;

class HttpsUrl extends HttpUrl
{
    public function isProtocolEncrypted()
    {
        return true;
    }
} 
