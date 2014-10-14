<?php


namespace Brera\PoC;


class HttpsUrl extends Url
{
    public function isProtocolEncrypted()
    {
        return true;
    }
} 