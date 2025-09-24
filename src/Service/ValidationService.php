<?php

declare(strict_types=1);

namespace App\Service;

use Rakit\Validation\Validator;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationService
{
    private static $validator;

    public function __construct()
    {
        if (!isset(self::$validator)) {
            self::$validator = new Validator([
                'required' => 'Value is required',
                'required_if' => 'Value is required when :field is :value',
                'required_unless' =>
                    'Value is required unless :field is :value',
                'required_with' => 'Value is required when :fields is present',
                'required_without' =>
                    'Value is required when :fields is not present',
                'required_with_all' =>
                    'Value is required when all of :fields are present',
                'required_without_all' =>
                    'Value is required when none of :fields are present',
                'uploaded_file' =>
                    'Value must be a valid uploaded file of size :min to :max and extension :extensions',
                'mimes' => 'Value must be a file of type: :extensions',
                'default' => 'Value has been set to default',
                'defaults' => 'Value has been set to default values',
                'email' => 'Value must be a valid email address',
                'uppercase' => 'Value must be uppercase',
                'lowercase' => 'Value must be lowercase',
                'json' => 'Value must be a valid JSON string',
                'alpha' => 'Value may only contain letters',
                'numeric' => 'Value must be a number',
                'alpha_num' => 'Value may only contain letters and numbers',
                'alpha_dash' =>
                    'Value may only contain letters, numbers, dashes, and underscores',
                'alpha_spaces' => 'Value may only contain letters and spaces',
                'in' => 'Selected value is invalid',
                'not_in' => 'Selected value is invalid',
                'min' => 'Value must be at least :min',
                'max' => 'Value may not be greater than :max',
                'between' => 'Value must be between :min and :max',
                'digits' => 'Value must be :value digits',
                'digits_between' =>
                    'Value must be between :min and :max digits',
                'url' => 'Value format is invalid',
                'integer' => 'Value must be an integer',
                'boolean' => 'Value must be true or false',
                'ip' => 'Value must be a valid IP address',
                'ipv4' => 'Value must be a valid IPv4 address',
                'ipv6' => 'Value must be a valid IPv6 address',
                'extension' => 'Value must be a file of type: :extensions',
                'array' => 'Value must be an array',
                'same' => 'Value must match :field',
                'regex' => 'Value format is invalid',
                'date' => 'Value is not a valid date in format :format',
                'accepted' => 'Value must be accepted',
                'present' => 'Value field must be present',
                'different' => 'Value must be different from :field',
                'after' => 'Value must be a date after :date',
                'before' => 'Value must be a date before :date',
                'callback' => 'Value failed validation',
                'nullable' => 'Value may be null',
            ]);
        }
    }
    /**
     * @param array<int,mixed> $values
     * @param array<int,mixed> $rules
     */
    public function validate(array $values, array $rules): ?array
    {
        $validation = self::$validator->validate($values, $rules);

        if ($validation->fails()) {
            return $validation->errors()->firstOfAll();
        }

        return null;
    }

    public function formatErrors(
        ConstraintViolationListInterface $errors,
    ): ?array {
        $formatted = null;
        foreach ($errors as $error) {
            $formatted[$error->getPropertyPath()] = $error->getMessage();
        }
        return $formatted;
    }
}
