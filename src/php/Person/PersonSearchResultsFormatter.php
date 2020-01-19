<?php
declare(strict_types=1);

namespace Hipper\Person;

use Hipper\DateTime\TimestampFormatter;
use Hipper\Organization\OrganizationModel;

class PersonSearchResultsFormatter
{
    use \Hipper\Search\SearchResultsFormatterTrait;

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
                    'raw_snippet' => $this->getSnippet($result, ['job_role_or_title_snippet']),
                    'description' => $this->getDescription($result),
                    'route' => '',
                    'type' => 'person',
                    'timestamp' => $this->timestampFormatter->format($result['created'], $displayTimeZone),
                    'timestamp_label' => 'Joined',
                ];
            },
            $results
        );
    }

    private function getDescription(array $result): string
    {
        if (!empty($result['job_role_or_title'])) {
            return $result['job_role_or_title'];
        }

        return '';
    }
}
