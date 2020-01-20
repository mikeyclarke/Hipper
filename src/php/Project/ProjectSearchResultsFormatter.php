<?php
declare(strict_types=1);

namespace Hipper\Project;

use Hipper\DateTime\TimestampFormatter;
use Hipper\Organization\OrganizationModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProjectSearchResultsFormatter
{
    use \Hipper\Search\SearchResultsFormatterTrait;

    private const ROUTE_NAME = 'front_end.app.project.show';

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
                    'initials' => mb_strtoupper(mb_substr(trim($result['name']), 0, 1)),
                    'raw_snippet' => $this->getSnippet($result, ['description_snippet']),
                    'description' => $result['description'] ?? '',
                    'route' => $this->router->generate(
                        self::ROUTE_NAME,
                        [
                            'subdomain' => $organization->getSubdomain(),
                            'project_url_id' => $result['url_id'],
                        ]
                    ),
                    'type' => 'project',
                    'timestamp' => $this->timestampFormatter->format($result['created'], $displayTimeZone),
                    'timestamp_label' => 'Created',
                ];
            },
            $results
        );
    }
}
