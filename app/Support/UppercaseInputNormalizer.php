<?php

namespace App\Support;

final class UppercaseInputNormalizer
{
    /**
     * Normalize selected human-readable input values without changing
     * identifiers, enum keys, email addresses, or other machine values.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, string>  $fields
     * @return array<string, mixed>
     */
    public static function normalize(array $data, array $fields): array
    {
        foreach ($fields as $field) {
            if (! array_key_exists($field, $data) || ! is_string($data[$field])) {
                continue;
            }

            $data[$field] = mb_strtoupper(trim($data[$field]), 'UTF-8');
        }

        return $data;
    }
}
