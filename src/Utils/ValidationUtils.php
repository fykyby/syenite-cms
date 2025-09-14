<?php

namespace App\Utils;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationUtils
{
    public static function formatErrors(ConstraintViolationListInterface $errors): ?array
    {
        $formatted = null;
        foreach ($errors as $error) {
            $formatted[$error->getPropertyPath()] = $error->getMessage();
        }
        return $formatted;
    }
}