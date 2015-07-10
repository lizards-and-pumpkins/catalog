<?php


namespace Brera\Http;

class ResourceNotFoundRequestHandler implements HttpRequestHandler
{
    /**
     * @param HttpRequest $request
     * @return HttpResponse
     */
    public function process(HttpRequest $request)
    {
        return new HttpResourceNotFoundResponse();
    }

    /**
     * @return bool
     */
    public function canProcess()
    {
        return true;
    }
}
