<?php


namespace LizardsAndPumpkins\Http;

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
     * @param HttpRequest $request
     * @return bool
     */
    public function canProcess(HttpRequest $request)
    {
        return true;
    }
}
