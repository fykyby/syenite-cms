<?php

declare(strict_types=1);

namespace App\Service;

class DataTransformer
{
    public static function attachValuesAndErrors(
        array $fields,
        array $data,
        array $errors,
    ): array {
        foreach ($fields as &$field) {
            $key = $field['key'];

            if ($field['type'] === 'array' && isset($field['fields'])) {
                $field['value'] = [];

                if (!empty($data[$key]) && is_array($data[$key])) {
                    foreach ($data[$key] as $index => $item) {
                        $arrayItemErrors = $errors[$key][$index] ?? [];
                        $field['value'][] = [
                            'fields' => self::attachValuesAndErrors(
                                $field['fields'],
                                $item,
                                $arrayItemErrors,
                            ),
                        ];
                    }
                }
            } elseif (
                $field['type'] === 'fieldset' &&
                isset($field['fields'])
            ) {
                $fieldsetData = $data[$key] ?? [];
                $fieldsetErrors = $errors[$key] ?? [];

                $field['fields'] = self::attachValuesAndErrors(
                    $field['fields'],
                    $fieldsetData,
                    $fieldsetErrors,
                );
            } elseif ($field['type'] === 'image') {
                $field['value'] = [
                    'url' => $data[$key]['url'] ?? null,
                    'alt' => $data[$key]['alt'] ?? null,
                    'name' => $data[$key]['name'] ?? null,
                    'variants' => $data[$key]['variants'] ?? null,
                    'type' => $data[$key]['type'] ?? null,
                ];
                $field['error'] = $errors[$key] ?? null;
            } else {
                $field['value'] = $data[$key] ?? null;
                $field['error'] = $errors[$key] ?? null;
            }
        }

        return $fields;
    }

    public static function buildValidationDataAndRules(
        array $fields,
        array $data,
        string $prefix = '',
    ): array {
        $validationData = [];
        $validationRules = [];

        foreach ($fields as $field) {
            $key = $field['key'];
            $fullKey = $prefix === '' ? $key : "{$prefix}.{$key}";

            if ($field['type'] === 'array' && isset($field['fields'])) {
                $validationData[$key] = [];

                if (!empty($data[$key]) && is_array($data[$key])) {
                    foreach ($data[$key] as $index => $item) {
                        $nestedResult = self::buildValidationDataAndRules(
                            $field['fields'],
                            $item,
                            "{$fullKey}.{$index}",
                        );

                        $validationData[$key][] = $nestedResult['data'];
                        $validationRules = array_merge(
                            $validationRules,
                            $nestedResult['rules'],
                        );
                    }
                }
            } elseif (
                $field['type'] === 'fieldset' &&
                isset($field['fields'])
            ) {
                $validationData[$key] = [];

                $nestedResult = self::buildValidationDataAndRules(
                    $field['fields'],
                    $data[$key] ?? [],
                    $fullKey,
                );

                $validationData[$key] = $nestedResult['data'];
                $validationRules = array_merge(
                    $validationRules,
                    $nestedResult['rules'],
                );
            } elseif ($field['type'] === 'media') {
                $validationData[$key] = [
                    'url' => $data[$key]['url'] ?? null,
                ];

                if (!empty($field['rules'])) {
                    $validationRules["{$fullKey}.url"] = $field['rules'];
                }
            } else {
                $validationData[$key] = $data[$key] ?? null;
                if (!empty($field['rules'])) {
                    $validationRules[$fullKey] = $field['rules'];
                }
            }
        }

        return ['data' => $validationData, 'rules' => $validationRules];
    }
}
