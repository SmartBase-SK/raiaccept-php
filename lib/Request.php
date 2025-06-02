<?php

namespace Raiaccept\RaiacceptApiClient;

class Request
{
    // header keys to omit from debug logging to file
    public const HIDDEN_HEADER_FIELDS = array(
        'Authorization',
    );

    public $method;
    public $url;
    public $headers;
    public $httpBody;

    public function __construct($method, $url, $headers, $httpBody = null)
    {
        $this->method = $method;
        $this->url = $url;
        $this->headers = $headers;
        $this->httpBody = $httpBody;
    }

    public function getUri()
    {
        return $this->url;
    }

    public function __toString()
    {
        $headersString = '';
        foreach ($this->headers as $key => $value) {
            if ( in_array($key, self::HIDDEN_HEADER_FIELDS ) ) {
                $headersString .= "$key: HIDDEN\n";
            } else {
                $headersString .= "$key: $value\n";
            }
        }

        $httpBodyString = $this->httpBody ? $this->httpBody : 'No body';

        return sprintf(
            "Request:\nMethod: %s\nURL: %s\nHeaders:\n%sBody:\n%s",
            $this->method,
            $this->url,
            $headersString,
            $httpBodyString
        );
    }
}
