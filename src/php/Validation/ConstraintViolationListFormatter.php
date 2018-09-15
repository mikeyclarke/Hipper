<?php
namespace hleo\Validation;

use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class ConstraintViolationListFormatter
{
    public static function format(ConstraintViolationList $violations)
    {
        $errors = [];

        foreach ($violations as $violation) {
            $key = self::formatViolationPropertyPath($violation);
            if (!isset($errors[$key])) {
                $errors[$key] = [];
            }
            $errors[$key][] = $violation->getMessage();
        }

        return $errors;
    }

    private static function formatViolationPropertyPath(ConstraintViolation $violation)
    {
        return preg_replace(
            ['(^\[)', '(\]\[)', '(\]$)'],
            ['', '.', ''],
            $violation->getPropertyPath()
        );
    }
}
