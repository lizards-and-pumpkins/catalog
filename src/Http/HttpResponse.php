<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\Http;

interface HttpResponse
{
    const STATUS_OK = 200;
    const STATUS_ACCEPTED = 202;
    const STATUS_BAD_REQUEST = 400;
    const STATUS_NOT_FOUND = 404;

    public function getBody(): string;

    public function getHeaders(): HttpHeaders;

    public function getStatusCode(): int;

    public function send();
}
