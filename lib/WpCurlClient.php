<?php

namespace Raiaccept\RaiacceptApiClient;

class WpCurlClient
{
    /**
     * @var bool
     */
    public $log_to_file = false;

    public function __construct($log_to_file = false)
    {
        if ($log_to_file == 'yes') {
            $this->log_to_file = true;
        } else {
            $this->log_to_file = false;
        }
    }

    public function send(Request $request, bool $omit_logging=false)
    {
        $url = $request->url;
        $args = [
            'method' => $request->method,
            'headers' => $request->headers,
            'body' => $request->httpBody,
            'timeout' => 45, // You can set a timeout if necessary
        ];

        // Initialize logger
        $logger = new WpFileLogger($this->log_to_file);
        if (!$omit_logging) {
            $logger->info('Request: ' . (string) $request);
        }

        // Perform the request
        $response = wp_remote_request($url, $args);

        // Check for errors
        if (is_wp_error($response)) {
            $error = $response->get_error_message();
            $logger->info('Response error: ' . $error);

            return $error;
        }

        // Extract response components
        $response_body = wp_remote_retrieve_body($response);
        $response_code = wp_remote_retrieve_response_code($response);
        $response_headers = wp_remote_retrieve_headers($response);

        // Log response
        if (!$omit_logging) {
            $logger->info('Response success(status: ' . $response_code . '): ' . $response_body);
        }

        // Format headers properly
        $formatted_headers = [];
        foreach ($response_headers as $key => $value) {
            $formatted_headers[strtolower($key)] = $value;
        }

        return new HttpResponse(
            $response_body,
            $formatted_headers,
            $response_code
        );
    }
}
