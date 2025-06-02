<?php
/**
 * ErrorResponse
 *
 * PHP version 7.4
 *
 * @category Class
 */

namespace Raiaccept\RaiacceptApiClient\Model;

class ErrorResponse {
    public string $traceId;
    public string $timestamp;
    public string $message;
    public string $status;
    public array $errors;

    public static function fromArray(array $data): self {
        $instance = new self();
        $instance->traceId = $data['traceId'] ?? '';
        $instance->timestamp = $data['timestamp'] ?? '';
        $instance->message = $data['message'] ?? '';
        $instance->status = $data['status'] ?? '';
        $instance->status = $data['status'] ?? '';
        $instance->errors = $data['errors'] ?? [];
        return $instance;
    }
}
