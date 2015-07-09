<?php

namespace Brera\Api\Stubs;

use Brera\Api\ApiRequestHandler;
use Brera\Http\HttpRequest;

class StubApiRequestHandler extends ApiRequestHandler
{
    const DUMMY_BODY_CONTENT = 'dummy';

    /**
     * @param HttpRequest $request
     * @return string
     */
    protected function getResponseBody(HttpRequest $request)
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
