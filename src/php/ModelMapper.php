<?php
declare(strict_types=1);

namespace Hipper;

class ModelMapper
{
    public function mapProperties($model, array $fields, array $properties): void
    {
        foreach ($properties as $key => $value) {
            if (!isset($fields[$key])) {
                continue;
            }

            $methodName = 'set' . $fields[$key];

            if (!is_callable([$model, $methodName])) {
                continue;
            }

            call_user_func([$model, $methodName], $value);
        }
    }
}
