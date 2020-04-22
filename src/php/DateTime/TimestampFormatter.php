<?php
declare(strict_types=1);

namespace Hipper\DateTime;

use Carbon\Carbon;
use RuntimeException;

class TimestampFormatter
{
    public function format(string $timestamp, string $displayTimeZone = 'UTC'): array
    {
        $dateTime = Carbon::createFromFormat('Y-m-d H:i:s.u', $timestamp);

        // @phpstan-ignore-next-line
        if (false === $dateTime) {
            throw new RuntimeException('DateTime could not be created from format');
        }
        $dateTime = $dateTime->tz($displayTimeZone);

        return [
            'time_ago' => $dateTime->diffForHumans(['options' => Carbon::JUST_NOW | Carbon::ONE_DAY_WORDS]),
            'unix' => $dateTime->unix(),
            'utc_datetime' => $dateTime->toISOString(),
            'verbose' => $dateTime->toDayDateTimeString(),
        ];
    }
}
