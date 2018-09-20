<?php
declare(strict_types=1);

namespace Lithos\Validation;

use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class ConstraintViolationListFormatter
{
    public static function format(ConstraintViolationList $violations): array
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

    private static function formatViolationPropertyPath(ConstraintViolation $violation): string
    {
        return preg_replace(
            ['(^\[)', '(\]\[)', '(\]$)'],
            ['', '.', ''],
            $violation->getPropertyPath()
        );
    }
}
