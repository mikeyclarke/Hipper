<?php
declare(strict_types=1);

namespace Hipper\Person;

use Hipper\DateTime\TimestampFormatter;
use Hipper\Organization\OrganizationModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PersonSearchResultsFormatter
{
    use \Hipper\Search\SearchResultsFormatterTrait;

    private const ROUTE_NAME = 'front_end.app.person.show';

    private TimestampFormatter $timestampFormatter;
    private UrlGeneratorInterface $router;

    public function __construct(
        TimestampFormatter $timestampFormatter,
        UrlGeneratorInterface $router
    ) {
        $this->timestampFormatter = $timestampFormatter;
        $this->router = $router;
    }

    public function format(OrganizationModel $organization, string $displayTimeZone, array $results): array
    {
        return array_map(
            function ($result) use ($displayTimeZone, $organization) {
                return [
                    'name' => $result['name'],
                    'initials' => $result['abbreviated_name'],
                    'raw_snippet' => $this->getSnippet($result, ['job_role_or_title_snippet']),
                    'description' => $this->getDescription($result),
                    'route' => $this->router->generate(
                        self::ROUTE_NAME,
                        [
                            'subdomain' => $organization->getSubdomain(),
                            'url_id' => $result['url_id'],
                            'username' => $result['username'],
                        ]
                    ),
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
