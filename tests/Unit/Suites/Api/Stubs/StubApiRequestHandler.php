<?php

namespace Brera\Api\Stubs;

use Brera\Api\ApiRequestHandler;

class StubApiRequestHandler extends ApiRequestHandler
{
    const DUMMY_BODY_CONTENT = 'dummy';

    /**
     * @return string
     */
    protected function getResponseBody()
    {
        return self::DUMMY_BODY_CONTENT;
    }

    /**
     * @return bool
     */
    public function canProcess()
    {
        return true;
    }
}
