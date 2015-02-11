<?php


namespace Brera\Http;

class ResourceNotFoundRequestHandler implements HttpRequestHandler
{
    /**
     * @return HttpResponse
     */
    public function process()
    {
        return new HttpResourceNotFoundResponse();
    }
}
