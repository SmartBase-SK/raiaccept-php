<?php

namespace Raiaccept\RaiacceptApiClient\Model;

class SanitizedInvalidArgumentException extends \InvalidArgumentException
{
    public function __construct(string $message = '', int $code = 0, ?\Throwable $previous = null)
    {
        if (function_exists('esc_html')) {
            parent::__construct(esc_html($message), $code, $previous);
        } else {
            parent::__construct($message, $code, $previous);
        }
    }
}
