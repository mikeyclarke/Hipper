<?php
declare(strict_types=1);

namespace Hipper\Team;

use Hipper\DateTime\TimestampFormatter;
use Hipper\Organization\OrganizationModel;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TeamsListFormatter
{
    const TEAM_ROUTE_NAME = 'front_end.app.team.show';

    private TimestampFormatter $timestampFormatter;
    private UrlGeneratorInterface $router;

    public function __construct(
        TimestampFormatter $timestampFormatter,
        UrlGeneratorInterface $router
    ) {
        $this->timestampFormatter = $timestampFormatter;
        $this->router = $router;
    }

    public function format(OrganizationModel $organization, array $teams, string $displayTimeZone): array
    {
        $result = array_map(
            function ($team) use ($displayTimeZone, $organization) {
                return [
                    'created' => $this->timestampFormatter->format($team['created'], $displayTimeZone),
                    'description' => $this->getTeamDescription($team),
                    'id' => $team['id'],
                    'name' => $team['name'],
                    'route' => $this->router->generate(
                        self::TEAM_ROUTE_NAME,
                        [
                            'subdomain' => $organization->getSubdomain(),
                            'team_url_id' => $team['url_id'],
                        ]
                    ),
                ];
            },
            $teams
        );

        return $result;
    }

    private function getTeamDescription(array $team): string
    {
        if (!empty($team['description'])) {
            return $team['description'];
        }

        return 'This team doesn’t have a description yet.';
    }
}
