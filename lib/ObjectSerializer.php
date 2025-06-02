<?php
/**
 * ObjectSerializer
 *
 * PHP version 7.4
 *
 * @category Class
 */

namespace Raiaccept\RaiacceptApiClient;

use Raiaccept\RaiacceptApiClient\Api\RaiAcceptAPIApi;
use Raiaccept\RaiacceptApiClient\Model\SanitizedInvalidArgumentException;

class ObjectSerializer
{
    public const BOOLEAN_FORMAT_INT = 'int';
    public const BOOLEAN_FORMAT_STRING = 'string';

    /** @var string */
    private static $dateTimeFormat = \DateTime::ATOM;

    /**
     * Serialize data
     *
     * @param mixed $data the data to serialize
     * @param string $type the OpenAPIToolsType of the data
     * @param string $format the format of the OpenAPITools type of the data
     *
     * @return scalar|object|array|null serialized form of $data
     */
    public static function sanitizeForSerialization($data, $type = null, $format = null)
    {
        if (is_scalar($data) || null === $data) {
            return $data;
        }

        if ($data instanceof \DateTime) {
            return ($format === 'date') ? $data->format('Y-m-d') : $data->format(self::$dateTimeFormat);
        }

        if (is_array($data)) {
            foreach ($data as $property => $value) {
                $data[$property] = self::sanitizeForSerialization($value);
            }

            return $data;
        }

        if (is_object($data)) {
            $values = [];
            foreach ($data as $property => $value) {
                $values[$property] = self::sanitizeForSerialization($value);
            }
            return (object) $values;
        } else {
            return (string) $data;
        }
    }

    /**
     * Take value and turn it into a string suitable for inclusion in
     * the path, by url-encoding.
     *
     * @param string $value a string which will be part of the path
     *
     * @return string the serialized object
     */
    public static function toPathValue($value)
    {
        return rawurlencode(self::toString($value));
    }

    /**
     * Take value and turn it into a string suitable for inclusion in
     * the parameter. If it's a string, pass through unchanged
     * If it's a datetime object, format it in ISO8601
     * If it's a boolean, convert it to "true" or "false".
     *
     * @param float|int|bool|\DateTime $value the value of the parameter
     *
     * @return string the header string
     */
    public static function toString($value)
    {
        if ($value instanceof \DateTime) { // datetime in ISO8601 format
            return $value->format(self::$dateTimeFormat);
        } elseif (is_bool($value)) {
            return $value ? 'true' : 'false';
        } else {
            return (string) $value;
        }
    }

    /**
     * Deserialize a JSON string into an object
     *
     * @param mixed $data object or primitive to be deserialized
     * @param string $class class name is passed as a string
     * @param string[] $httpHeaders HTTP headers
     *
     * @return object|array|null a single or an array of $class instances
     */
    public static function deserialize($data, $class, $httpHeaders = null)
    {
        if (null === $data) {
            return null;
        }

        if (strcasecmp(substr($class, -2), '[]') === 0) {
            $data = is_string($data) ? RaiAcceptAPIApi::json_decode($data) : $data;

            if (!is_array($data)) {
                $sanitized_class = sanitize_text_field($class);
                throw new SanitizedInvalidArgumentException(esc_html("Invalid array '$sanitized_class'"));
            }

            $subClass = substr($class, 0, -2);
            $values = [];
            foreach ($data as $key => $value) {
                $values[] = self::deserialize($value, $subClass, null);
            }

            return $values;
        }

        if (preg_match('/^(array<|map\[)/', $class)) { // for associative array e.g. array<string,int>
            $data = is_string($data) ? RaiAcceptAPIApi::json_decode($data) : $data;
            settype($data, 'array');
            $inner = substr($class, 4, -1);
            $deserialized = [];
            if (strrpos($inner, ',') !== false) {
                $subClass_array = explode(',', $inner, 2);
                $subClass = $subClass_array[1];
                foreach ($data as $key => $value) {
                    $deserialized[$key] = self::deserialize($value, $subClass, null);
                }
            }

            return $deserialized;
        }

        if ($class === 'object') {
            settype($data, 'array');

            return $data;
        } elseif ($class === 'mixed') {
            settype($data, gettype($data));

            return $data;
        }

        if ($class === '\DateTime') {
            // Some APIs return an invalid, empty string as a
            // date-time property. DateTime::__construct() will return
            // the current time for empty input which is probably not
            // what is meant. The invalid empty string is probably to
            // be interpreted as a missing field/value. Let's handle
            // this graceful.
            if (!empty($data)) {
                try {
                    return new \DateTime($data);
                } catch (\Exception $exception) {
                    // Some APIs return a date-time with too high nanosecond
                    // precision for php's DateTime to handle.
                    // With provided regexp 6 digits of microseconds saved
                    return new \DateTime(self::sanitizeTimestamp($data));
                }
            } else {
                return null;
            }
        }

        /* @psalm-suppress ParadoxicalCondition */
        if (in_array($class, [
            '\DateTime',
            'array',
            'bool',
            'boolean',
            'byte',
            'float',
            'int',
            'integer',
            'mixed',
            'number',
            'object',
            'string',
            'void',
        ], true)) {
            settype($data, $class);

            return $data;
        }

        if (method_exists($class, 'getAllowableEnumValues')) {
            return $data;
        } else {
            $data = is_string($data) ? RaiAcceptAPIApi::json_decode($data) : $data;

            if (is_array($data) && method_exists($class, 'fromArray')) {
                return $class::fromArray($data);
            }

            $instance = new $class();
            foreach ($data as $property => $value) {
                $instance->$property = self::sanitizeForSerialization($value);
            }
            return $instance;
        }
    }

    /**
     * Shorter timestamp microseconds to 6 digits length.
     *
     * @param string $timestamp Original timestamp
     *
     * @return string the shorten timestamp
     */
    public static function sanitizeTimestamp($timestamp)
    {
        if (!is_string($timestamp)) {
            return $timestamp;
        }

        return preg_replace('/(:\d{2}.\d{6})\d*/', '$1', $timestamp);
    }
}
