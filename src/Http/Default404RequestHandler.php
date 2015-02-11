<?php


namespace Brera\Http;

class Default404RequestHandler implements HttpRequestHandler
{
    /**
     * @return HttpResponse
     */
    public function process()
    {
        return new HttpResourceNotFoundResponse();
    }
}
