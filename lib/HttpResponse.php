<?php

namespace Raiaccept\RaiacceptApiClient;

class HttpResponse
{
    private $body;
    private $headers;
    private $statusCode;

    public function __construct($body, $headers, $statusCode)
    {
        $this->body = $body;
        $this->headers = $headers;
        $this->statusCode = $statusCode;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }
}
