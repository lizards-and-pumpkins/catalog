<?php

namespace Brera\Http;

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
