<?php
declare(strict_types=1);

namespace Hipper\FrontEnd\App\Controller\Team;

use Hipper\Team\TeamRepository;
use Hipper\Team\TeamsListFormatter;
use Hipper\TimeZone\TimeZoneFromRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as Twig;

class TeamsController
{
    private $teamRepository;
    private $teamsListFormatter;
    private $timeZoneFromRequest;
    private $twig;

    public function __construct(
        TeamRepository $teamRepository,
        TeamsListFormatter $teamsListFormatter,
        TimeZoneFromRequest $timeZoneFromRequest,
        Twig $twig
    ) {
        $this->teamRepository = $teamRepository;
        $this->teamsListFormatter = $teamsListFormatter;
        $this->timeZoneFromRequest = $timeZoneFromRequest;
        $this->twig = $twig;
    }

    public function getAction(Request $request): Response
    {
        $organization = $request->attributes->get('organization');
        $teams = $this->teamRepository->getAll($organization->getId());
        $displayTimeZone = $this->timeZoneFromRequest->get($request);

        $teamsList = $this->teamsListFormatter->format($teams, $displayTimeZone);

        $context = [
            'html_title' => 'Teams',
            'teams_list' => $teamsList,
        ];

        return new Response(
            $this->twig->render('team/teams_list.twig', $context)
        );
    }
}
