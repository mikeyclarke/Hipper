<?php
declare(strict_types=1);

namespace Hipper\Project;

use Hipper\Organization\OrganizationModel;
use Carbon\Carbon;
use RuntimeException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ProjectsListFormatter
{
    const PROJECT_ROUTE_NAME = 'front_end.app.project.show';

    private UrlGeneratorInterface $router;

    public function __construct(
        UrlGeneratorInterface $router
    ) {
        $this->router = $router;
    }

    public function format(OrganizationModel $organization, array $projects, string $displayTimeZone): array
    {
        $result = array_map(
            function ($project) use ($displayTimeZone, $organization) {
                $dateTime = Carbon::createFromFormat('Y-m-d H:i:s.u', $project['created']);
                if (false === $dateTime) {
                    throw new RuntimeException('DateTime could not be created from format');
                }
                $dateTime = $dateTime->tz($displayTimeZone);

                return [
                    'created' => [
                        'utc_datetime' => $dateTime->toISOString(),
                        'time_ago' => $dateTime->diffForHumans(),
                        'verbose' => $dateTime->toDayDateTimeString(),
                    ],
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
