<?php
declare(strict_types=1);

namespace Hipper\Person;

use Hipper\DateTime\TimestampFormatter;
use Hipper\Organization\OrganizationModel;

class PersonSearchResultsFormatter
{
    private TimestampFormatter $timestampFormatter;

    public function __construct(
        TimestampFormatter $timestampFormatter
    ) {
        $this->timestampFormatter = $timestampFormatter;
    }

    public function format(OrganizationModel $organization, string $displayTimeZone, array $results): array
    {
        return array_map(
            function ($result) use ($displayTimeZone) {
                return [
                    'name' => $result['name'],
                    'initials' => $result['abbreviated_name'],
                    'displayBody' => $this->getDisplayBody($result),
                    'route' => '',
                    'type' => 'person',
                    'timestamp' => $this->timestampFormatter->format($result['created'], $displayTimeZone),
                    'timestamp_label' => 'Joined',
                ];
            },
            $results
        );
    }

    private function getDisplayBody(array $result): string
    {
        if (!empty(trim($result['job_role_or_title_snippet']))) {
            return $result['job_role_or_title_snippet'];
        }

        if (!empty($result['job_role_or_title'])) {
            return $result['job_role_or_title'];
        }

        return '';
    }
}
