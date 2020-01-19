<?php
declare(strict_types=1);

namespace Hipper\Project;

use Hipper\DateTime\TimestampFormatter;
use Hipper\Organization\OrganizationModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProjectsListFormatter
{
    const PROJECT_ROUTE_NAME = 'front_end.app.project.show';

    private TimestampFormatter $timestampFormatter;
    private UrlGeneratorInterface $router;

    public function __construct(
        TimestampFormatter $timestampFormatter,
        UrlGeneratorInterface $router
    ) {
        $this->timestampFormatter = $timestampFormatter;
        $this->router = $router;
    }

    public function format(OrganizationModel $organization, array $projects, string $displayTimeZone): array
    {
        $result = array_map(
            function ($project) use ($displayTimeZone, $organization) {
                return [
                    'created' => $this->timestampFormatter->format($project['created'], $displayTimeZone),
                    'description' => $this->getProjectDescription($project),
                    'id' => $project['id'],
                    'name' => $project['name'],
                    'route' => $this->router->generate(
                        self::PROJECT_ROUTE_NAME,
                        [
                            'project_url_id' => $project['url_id'],
                            'subdomain' => $organization->getSubdomain(),
                        ]
                    ),
                ];
            },
            $projects
        );

        return $result;
    }

    private function getProjectDescription(array $project): string
    {
        if (!empty($project['description'])) {
            return $project['description'];
        }

        return 'This project doesn’t have a description yet.';
    }
}
