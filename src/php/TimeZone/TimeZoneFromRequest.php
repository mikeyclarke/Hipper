<?php
declare(strict_types=1);

namespace Hipper\TimeZone;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Timezone;
use Symfony\Component\Validator\Validation;

class TimeZoneFromRequest
{
    const COOKIE_NAME = 'tz';

    public function get(Request $request, string $default = 'UTC'): string
    {
        if (!$request->cookies->has(self::COOKIE_NAME)) {
            return $default;
        }

        $timeZone = $request->cookies->get('tz');
        if (!$this->isTimeZoneSupported($timeZone)) {
            return $default;
        }

        return $timeZone;
    }

    private function isTimeZoneSupported(string $timeZone): bool
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($timeZone, [new Timezone]);
        if (count($violations) > 0) {
            return false;
        }
        return true;
    }
}
