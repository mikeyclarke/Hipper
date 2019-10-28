<?php
declare(strict_types=1);

namespace Hipper\Team;

use Carbon\Carbon;
use RuntimeException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class TeamsListFormatter
{
    const TEAM_ROUTE_NAME = 'front_end.app.team.show';

    private $router;

    public function __construct(
        UrlGeneratorInterface $router
    ) {
        $this->router = $router;
    }

    public function format(array $teams, string $displayTimeZone): array
    {
        $result = array_map(
            function ($team) use ($displayTimeZone) {
                $dateTime = Carbon::createFromFormat('Y-m-d H:i:s.u', $team['created']);
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
                    'description' => $this->getTeamDescription($team),
                    'id' => $team['id'],
                    'name' => $team['name'],
                    'route' => $this->router->generate(
                        self::TEAM_ROUTE_NAME,
                        ['team_url_id' => $team['url_id']]
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
