<?php

declare(strict_types=1);

namespace App\Utils;

use Rakit\Validation\Validator;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationUtils
{
    private static $validator;

    public static function init()
    {
        if (! isset(self::$validator)) {
            self::$validator = new Validator([
                'required' => 'Value is required.',
                'min' => 'Value must be at least :min characters long.',
                'max' => 'Value may not be longer than :max characters.',
                'email' => 'Value must be a valid email address.',
                'numeric' => 'Value must be a number.',
                'between' => 'Value must be between :min and :max.',
                'in' => 'Selected value is invalid.',
                'regex' => 'Value format is invalid.',
            ]);
        }
    }

    public static function validate(array $values, array $rules): ?array
    {
        $validation = self::$validator->validate($values, $rules);

        if ($validation->fails()) {
            return $validation->errors()->firstOfAll();
        }

        return null;
    }

    public static function formatErrors(ConstraintViolationListInterface $errors): ?array
    {
        $formatted = null;
        foreach ($errors as $error) {
            $formatted[$error->getPropertyPath()] = $error->getMessage();
        }
        return $formatted;
    }
}

ValidationUtils::init();