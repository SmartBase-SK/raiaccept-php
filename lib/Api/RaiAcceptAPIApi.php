<?php

namespace Raiaccept\RaiacceptApiClient\Api;

use Raiaccept\RaiacceptApiClient\ApiException;
use Raiaccept\RaiacceptApiClient\Model\SanitizedInvalidArgumentException;
use Raiaccept\RaiacceptApiClient\ObjectSerializer;
use Raiaccept\RaiacceptApiClient\Request;

class RaiAcceptAPIApi
{
    public const AUTH_URL = 'https://authenticate.raiaccept.com';
    public const AUTH_FLOW = 'USER_PASSWORD_AUTH';
    public const AUTH_CLIENT_ID = 'kr2gs4117arvbnaperqff5dml';
    public const API_URL = 'https://trapi.raiaccept.com';

    public const ACCEPTED_LANGUAGES = [
        'en',
        'de',
        'fr',
        'cs',
        'sk',
        'sr',
        'al',
        'ro',
        'pl',
        'hr',
    ];
    /**
     * @var object
     */
    protected $client;

    /**
     * @param object $client
     */
    public function __construct(
        ?object $client = null
    ) {
        $this->client = $client;
    }

    public function getAcceptedLanguages() {
        return self::ACCEPTED_LANGUAGES;
    }

    public function processRequest(Request $request, $target_structure, $error_structure, bool $omit_logging=false)
    {
        try {
            try {
                $response = $this->client->send($request, $omit_logging);
            } catch (RequestException $e) {
                throw new ApiException("[{$e->getCode()}] {$e->getMessage()}", (int) $e->getCode(), $e->getResponse() ? $e->getResponse()->getHeaders() : null, $e->getResponse() ? (string) $e->getResponse()->getBody() : null);
            } catch (ConnectException $e) {
                throw new ApiException("[{$e->getCode()}] {$e->getMessage()}", (int) $e->getCode(), null, null);
            }

            $statusCode = $response->getStatusCode();

            if ($statusCode < 200 || $statusCode > 299) {
                throw new ApiException(sprintf('[%d] Error connecting to the API (%s)', $statusCode, (string) $request->getUri(), (string) $response->getBody()), $statusCode, $response->getHeaders(), (string) $response->getBody(), (string) $request);
            }

            $body = static::json_decode($response->getBody(), true);
            if (!is_null($target_structure)) {
                $deserialized_content = call_user_func($target_structure . '::fromArray', $body);
            } else {
                $deserialized_content = null;
            }

            return [
                'object' => $deserialized_content,
                'response' => $response,
            ];
        } catch (ApiException $e) {
            switch ($e->getCode()) {
                case 400:
                    $data = ObjectSerializer::deserialize(
                        $e->getResponseBody(),
                        $error_structure,
                        $e->getResponseHeaders()
                    );
                    $e->setResponseObject($data);
                    break;
            }
            throw $e;
        }
    }

    public static function getAuthRequestHeaders()
    {
        return array(
            'Content-Type' => 'application/x-amz-json-1.1',
            'X-Amz-Target' => 'AWSCognitoIdentityProviderService.InitiateAuth',
        );
    }

    public static function json_decode(...$args)
    {
        return json_decode(...$args);
    }

    public static function json_encode(...$args)
    {
	    // Store the current precision
	    $ini_value = ini_get( 'serialize_precision' );

		// Set the new precision and export the variable
		ini_set( 'serialize_precision', -1 );

		if (function_exists('wp_json_encode')) {
            $result = wp_json_encode(...$args);
        } else {
            // fallback for non Wordpress environment
	        $result = json_encode(...$args); // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
        }

	    // Restore the previous value
	    ini_set( 'serialize_precision', $ini_value );
		return $result;
    }

    public function token($username, $password)
    {
        $request = $this->tokenRequest(self::AUTH_FLOW, $username, $password, self::AUTH_CLIENT_ID);

        return $this->processRequest($request, 'Raiaccept\RaiacceptApiClient\Model\AuthResponse', '\Raiaccept\RaiacceptApiClient\Model\ErrorResponse', true);
    }

    public function tokenRequest($auth_flow, $username, $password, $client_id)
    {
        // verify the required parameter '$auth_flow' is set
        if ($auth_flow === null || (is_array($auth_flow) && count($auth_flow) === 0)) {
            throw new SanitizedInvalidArgumentException('Missing the required parameter $auth_flow when calling tokenRequest');
        }

        // verify the required parameter '$username' is set
        if ($username === null || (is_array($username) && count($username) === 0)) {
            throw new SanitizedInvalidArgumentException('Missing the required parameter $username when calling tokenRequest');
        }

        // verify the required parameter '$password' is set
        if ($password === null || (is_array($password) && count($password) === 0)) {
            throw new SanitizedInvalidArgumentException('Missing the required parameter $password when calling tokenRequest');
        }

        // verify the required parameter '$client_id' is set
        if ($client_id === null || (is_array($client_id) && count($client_id) === 0)) {
            throw new SanitizedInvalidArgumentException('Missing the required parameter $client_id when calling tokenRequest');
        }

        $formParams = [
            'AuthFlow' => $auth_flow,
            'AuthParameters' => array(
                'USERNAME' => $username,
                'PASSWORD' => $password
            ),
            'ClientId' => $client_id,
        ];
        $httpBody = static::json_encode(ObjectSerializer::sanitizeForSerialization($formParams));
        $headers = $this->getAuthRequestHeaders();

        return new Request(
            'POST',
            self::AUTH_URL,
            $headers,
            $httpBody
        );
    }

    public function createOrderEntry($access_token, $create_order_request)
    {
        $request = $this->createOrderEntryRequest($access_token, $create_order_request);

        return $this->processRequest($request, 'Raiaccept\RaiacceptApiClient\Model\CreateOrderEntryResponse', '\Raiaccept\RaiacceptApiClient\Model\ErrorResponse');
    }

    public function createOrderEntryRequest($access_token, $create_order_request)
    {
        // verify the required parameter 'access_token' is set
        if ($access_token === null || (is_array($access_token) && count($access_token) === 0)) {
            throw new SanitizedInvalidArgumentException('Missing the required parameter $access_token when calling createOrderEntry');
        }

        // verify the required parameter 'create_order_request' is set
        if ($create_order_request === null || (is_array($create_order_request) && count($create_order_request) === 0)) {
            throw new SanitizedInvalidArgumentException('Missing the required parameter $create_order_request when calling createOrderEntry');
        }

        $resourcePath = '/orders';
        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        );

        $httpBody = static::json_encode(ObjectSerializer::sanitizeForSerialization($create_order_request));
        $httpBody = str_replace('\\\\n', '\\n', $httpBody);

        return new Request(
            'POST',
            self::API_URL . $resourcePath,
            $headers,
            $httpBody
        );
    }

    public function createPaymentSession($access_token, $create_order_request, $external_order_id)
    {
        $request = $this->createPaymentSessionRequest($access_token, $create_order_request, $external_order_id);

        return $this->processRequest($request, 'Raiaccept\RaiacceptApiClient\Model\CreatePaymentSessionResponse', '\Raiaccept\RaiacceptApiClient\Model\ErrorResponse');
    }

    public function createPaymentSessionRequest($access_token, $payment_session_request, $external_order_id)
    {
        // verify the required parameter 'access_token' is set
        if ($access_token === null || (is_array($access_token) && count($access_token) === 0)) {
            throw new SanitizedInvalidArgumentException('Missing the required parameter $access_token when calling createPaymentSession');
        }

        // verify the required parameter 'external_order_id' is set
        if ($external_order_id === null || (is_array($external_order_id) && count($external_order_id) === 0)) {
            throw new SanitizedInvalidArgumentException('Missing the required parameter $external_order_id when calling createPaymentSession');
        }

        // verify the required parameter 'payment_session_request' is set
        if ($payment_session_request === null || (is_array($payment_session_request) && count($payment_session_request) === 0)) {
            throw new SanitizedInvalidArgumentException('Missing the required parameter $payment_session_request when calling createPaymentSession');
        }

        $resourcePath = self::API_URL . "/orders/{$external_order_id}/checkout";
        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        );

        $httpBody = static::json_encode(ObjectSerializer::sanitizeForSerialization($payment_session_request));
        $httpBody = str_replace('\\\\n', '\\n', $httpBody);

        return new Request(
            'POST',
            $resourcePath,
            $headers,
            $httpBody
        );
    }

    public function getOrderDetails($access_token, $payment_id)
    {
        $request = $this->getOrderDetailsRequest($access_token, $payment_id);

        return $this->processRequest($request, '\Raiaccept\RaiacceptApiClient\Model\GetOrderDetailsResponse', '\Raiaccept\RaiacceptApiClient\Model\ErrorResponse');
    }

    public function getOrderDetailsRequest($access_token, $payment_id)
    {
        // verify the required parameter 'payment_id' is set
        if ($payment_id === null || (is_array($payment_id) && count($payment_id) === 0)) {
            throw new SanitizedInvalidArgumentException('Missing the required parameter $payment_id when calling getOrderDetailsRequest');
        }

        // verify the required parameter 'access_token' is set
        if ($access_token === null || (is_array($access_token) && count($access_token) === 0)) {
            throw new SanitizedInvalidArgumentException('Missing the required parameter $access_token when calling getOrderDetailsRequest');
        }

        $resourcePath = self::API_URL . '/orders/{payment-id}';
        $resourcePath = str_replace(
            '{payment-id}',
            ObjectSerializer::toPathValue($payment_id),
            $resourcePath
        );

        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        );
        return new Request(
            'GET',
            $resourcePath,
            $headers,
        );
    }

    public function getTransactionDetails($access_token, $order_id, $transaction_id)
    {
        $request = $this->getTransactionDetailsRequest($access_token, $order_id, $transaction_id);

        return $this->processRequest($request, '\Raiaccept\RaiacceptApiClient\Model\GetTransactionDetailsResponse', '\Raiaccept\RaiacceptApiClient\Model\ErrorResponse');
    }

    public function getTransactionDetailsRequest($access_token, $order_id, $transaction_id)
    {
        // verify the required parameter 'order_id' is set
        if ($order_id === null || (is_array($order_id) && count($order_id) === 0)) {
            throw new SanitizedInvalidArgumentException('Missing the required parameter $order_id when calling getTransactionDetailsRequest');
        }

        // verify the required parameter 'transaction_id' is set
        if ($transaction_id === null || (is_array($transaction_id) && count($transaction_id) === 0)) {
            throw new SanitizedInvalidArgumentException('Missing the required parameter $transaction_id when calling getTransactionDetailsRequest');
        }

        // verify the required parameter 'access_token' is set
        if ($access_token === null || (is_array($access_token) && count($access_token) === 0)) {
            throw new SanitizedInvalidArgumentException('Missing the required parameter $access_token when calling getTransactionDetailsRequest');
        }

        $encoded_order_id = ObjectSerializer::toPathValue($order_id);
        $encoded_transaction_id = ObjectSerializer::toPathValue($transaction_id);
        $resourcePath = self::API_URL . "/orders/{$encoded_order_id}/transactions/{$encoded_transaction_id}";

        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        );
        return new Request(
            'GET',
            $resourcePath,
            $headers,
        );
    }

    public function getOrderTransactions($access_token, $order_id)
    {
        $request = $this->getOrderTransactionsRequest($access_token, $order_id);

        return $this->processRequest($request, '\Raiaccept\RaiacceptApiClient\Model\GetOrderTransactionsResponse', '\Raiaccept\RaiacceptApiClient\Model\ErrorResponse');
    }

    public function getOrderTransactionsRequest($access_token, $order_id)
    {
        // verify the required parameter 'order_id' is set
        if ($order_id === null || (is_array($order_id) && count($order_id) === 0)) {
            throw new SanitizedInvalidArgumentException('Missing the required parameter $order_id when calling getTransactionDetailsRequest');
        }

        // verify the required parameter 'access_token' is set
        if ($access_token === null || (is_array($access_token) && count($access_token) === 0)) {
            throw new SanitizedInvalidArgumentException('Missing the required parameter $access_token when calling getTransactionDetailsRequest');
        }

        $encoded_order_id = ObjectSerializer::toPathValue($order_id);
        $resourcePath = self::API_URL . "/orders/{$encoded_order_id}/transactions";

        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        );
        return new Request(
            'GET',
            $resourcePath,
            $headers,
        );
    }

    public function refund($access_token, $order_id, $transaction_id, $request_obj)
    {
        $request = $this->getRefundRequest($access_token, $order_id, $transaction_id, $request_obj);

        return $this->processRequest($request, '\Raiaccept\RaiacceptApiClient\Model\RefundResponse', '\Raiaccept\RaiacceptApiClient\Model\ErrorResponse');
    }

    public function getRefundRequest($access_token, $order_id, $transaction_id, $request_obj)
    {
        // verify the required parameter 'order_id' is set
        if ($order_id === null || (is_array($order_id) && count($order_id) === 0)) {
            throw new SanitizedInvalidArgumentException('Missing the required parameter $order_id when calling getRefundRequest');
        }

        // verify the required parameter 'transaction_id' is set
        if ($transaction_id === null || (is_array($transaction_id) && count($transaction_id) === 0)) {
            throw new SanitizedInvalidArgumentException('Missing the required parameter $transaction_id when calling getRefundRequest');
        }

        // verify the required parameter 'access_token' is set
        if ($access_token === null || (is_array($access_token) && count($access_token) === 0)) {
            throw new SanitizedInvalidArgumentException('Missing the required parameter $access_token when calling getRefundRequest');
        }

        // verify the required parameter 'request_obj' is set
        if ($request_obj === null || (is_array($request_obj) && count($request_obj) === 0)) {
            throw new SanitizedInvalidArgumentException('Missing the required parameter $request_obj when calling getRefundRequest');
        }

        $encoded_order_id = ObjectSerializer::toPathValue($order_id);
        $encoded_transaction_id = ObjectSerializer::toPathValue($transaction_id);
        $resourcePath = self::API_URL . "/orders/{$encoded_order_id}/transactions/{$encoded_transaction_id}/refund";

        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $access_token,
        );

        $httpBody = static::json_encode(ObjectSerializer::sanitizeForSerialization($request_obj));
        $httpBody = str_replace('\\\\n', '\\n', $httpBody);

        return new Request(
            'POST',
            $resourcePath,
            $headers,
            $httpBody
        );
    }

}
