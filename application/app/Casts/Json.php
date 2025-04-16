<?php

namespace App\Casts;

use JsonException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class Json implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return array
     * @throws JsonException
     */
    public function get($model, $key, $value, $attributes): array
    {
        // If value is already an array, return it.
        if (is_array($value)) {
            return $value;
        }
        // If the value is empty or null, return an empty array.
        if (empty($value)) {
            return [];
        }
        try {
            $decoded = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            // In case JSON decoding fails, return empty array.
            return [];
        }
        // If the decoded result is not an array, it might be double-encoded.
        if (!is_array($decoded)) {
            try {
                $decoded = json_decode($decoded, true, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                return [];
            }
        }
        return is_array($decoded) ? $decoded : (array)$decoded;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  Model  $model
     * @param  string  $key
     * @param  mixed  $value
     * @param  array  $attributes
     * @return string
     * @throws JsonException
     */
    public function set($model, $key, $value, $attributes): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR);
    }
}
