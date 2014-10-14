<?php


namespace Brera\PoC;


class HttpsUrl extends HttpUrl
{
    public function isProtocolEncrypted()
    {
        return true;
    }
} 